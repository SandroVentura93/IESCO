<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\PedagogicalResource;
use App\Models\StoredFile;
use App\Support\StoredFileSections;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

/**
 * DriveProxyController
 *
 * Actúa como proxy entre el frontend y la Google Drive API v3.
 * La API Key nunca se expone al navegador.
 */
class DriveProxyController extends Controller
{
    /**
     * Archivos/carpetas del proyecto IJENT (raíz).
     */
    public function proyecto(): JsonResponse
    {
        $folderId = config('school.drive.proyecto_folder_id');

        return $this->listFolder($folderId, 'proyecto_root');
    }

    /**
     * Contenido de una subcarpeta del proyecto.
     * Parámetro GET: ?folder_id=XXXX
     */
    public function subfolder(Request $request): JsonResponse
    {
        $folderId = $request->query('folder_id', '');

        // Validar que solo contenga caracteres permitidos por Drive
        if (! preg_match('/^[\w-]+$/', $folderId)) {
            return response()->json(['error' => 'Invalid folder ID'], 422);
        }

        return $this->listFolder($folderId, "subfolder_{$folderId}");
    }

    /**
     * Archivos administrados localmente para IJENT, novedades y publicaciones.
     */
    public function localProject(Request $request): JsonResponse
    {
        $sections = StoredFileSections::labels(StoredFileSections::publicOptions());

        if ($request->filled('section')) {
            $data = $request->validate([
                'section' => 'required|in:'.implode(',', array_keys($sections)),
                'folder' => [
                    'nullable',
                    Rule::in(array_keys(StoredFileSections::subfolders($request->input('section')))),
                ],
                'course' => StoredFileSections::hasCourseHierarchy($request->input('section'))
                    && $request->filled('folder')
                    ? ['nullable', 'integer', Rule::exists('courses', 'id')]
                    : ['prohibited'],
                'category' => [
                    'nullable',
                    Rule::in(
                        StoredFileSections::hasCourseHierarchy($request->input('section'))
                            && $request->filled('folder')
                                ? array_keys(StoredFileSections::fileTypeSubfolders())
                                : []
                    ),
                ],
            ]);

            return response()->json([
                'files' => $this->localFilesForSection(
                    $data['section'],
                    $data['folder'] ?? null,
                    isset($data['course']) ? (int) $data['course'] : null,
                    $data['category'] ?? null
                ),
            ]);
        }

        $navigationProjectSections = StoredFileSections::projectOptions();
        unset($navigationProjectSections['Proyecto_IJENT']);

        $activeSections = StoredFile::publiclyAvailable()
            ->whereIn('section', array_keys($navigationProjectSections))
            ->distinct()
            ->pluck('section');
        $activeSections = $activeSections
            ->merge(StoredFileSections::courseHierarchySections())
            ->unique()
            ->values();

        $folders = $activeSections
            ->map(function (string $section) use ($sections) {
                return [
                    'id' => 'local_'.$section,
                    'name' => $sections[$section],
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'localSection' => $section,
                ];
            })
            ->values();

        return response()->json(['files' => $folders]);
    }

    /**
     * Novedades.
     */
    public function novedades(): JsonResponse
    {
        $folderId = config('school.drive.novedades_folder_id');

        return $this->listFolder($folderId, 'novedades');
    }

    /**
     * Publicaciones.
     */
    public function publicaciones(): JsonResponse
    {
        $folderId = config('school.drive.publicaciones_folder_id');

        return $this->listFolder($folderId, 'publicaciones');
    }

    private function localFilesForSection(
        string $section,
        ?string $folder = null,
        ?int $courseId = null,
        ?string $category = null
    ): array {
        $directory = StoredFileSections::directory($section);
        $subfolders = StoredFileSections::subfolders($section);
        $categories = StoredFileSections::fileTypeSubfolders();
        $courses = Course::query()->orderBy('name')->get(['id', 'name']);
        $course = $courseId === null ? null : $courses->firstWhere('id', $courseId);
        abort_if($courseId !== null && $course === null, 422, 'El curso seleccionado no es válido.');
        $files = StoredFile::publiclyAvailable()
            ->with(['course:id,name', 'user:id,name'])
            ->where('section', $section)
            ->where('path', 'like', $directory.'/%')
            ->latest('published_at')
            ->get()
            ->filter(function (StoredFile $file) use ($directory, $folder, $course, $category, $subfolders, $categories, $section) {
                $relativePath = substr($file->path, strlen($directory) + 1);
                $segments = explode('/', $relativePath);

                if ($folder === null) {
                    return count($segments) === 1;
                }

                $subfolderDirectory = $subfolders[$folder]['directory'] ?? null;
                if ($subfolderDirectory === null || (! StoredFileSections::hasCourseHierarchy($section) && $category !== null)) {
                    return false;
                }

                if ($course === null) {
                    $legacyCategoryDirectories = array_column($categories, 'directory');

                    if ($category !== null) {
                        $legacyCategoryDirectory = $categories[$category]['directory'] ?? null;

                        return StoredFileSections::hasCourseHierarchy($section)
                            && $legacyCategoryDirectory !== null
                            && count($segments) === 3
                            && $segments[0] === $subfolderDirectory
                            && $segments[1] === $legacyCategoryDirectory;
                    }

                    return StoredFileSections::hasCourseHierarchy($section)
                        && $segments[0] === $subfolderDirectory
                        && (
                            count($segments) === 2
                            || (count($segments) === 3 && in_array($segments[1], $legacyCategoryDirectories, true))
                        );
                }

                $courseDirectory = StoredFileSections::courseDirectory($course->id);
                if ($category === null) {
                    return count($segments) === 3
                        && $segments[0] === $subfolderDirectory
                        && $segments[1] === $courseDirectory
                        && (int) $file->course_id === (int) $course->id;
                }

                $categoryDirectory = $categories[$category]['directory'] ?? null;

                return $categoryDirectory !== null
                    && count($segments) === 4
                    && $segments[0] === $subfolderDirectory
                    && $segments[1] === $courseDirectory
                    && $segments[2] === $categoryDirectory
                    && (int) $file->course_id === (int) $course->id;
            })
            ->values();
        $pedagogicalResources = PedagogicalResource::query()
            ->whereIn('path', $files->pluck('path'))
            ->get(['path', 'week'])
            ->keyBy('path');
        $sectionLabels = StoredFileSections::labels(StoredFileSections::destinations());
        $fileTypeLabels = StoredFileSections::labels(StoredFileSections::fileTypeSubfolders());
        $files = $files->map(function (StoredFile $file) use ($section, $pedagogicalResources, $sectionLabels, $fileTypeLabels) {
            $pedagogicalResource = $pedagogicalResources->get($file->path);

            return [
                'id' => 'local-'.$file->id,
                'name' => $file->name,
                'mimeType' => $file->mime ?: 'application/octet-stream',
                'modifiedTime' => $file->published_at?->toIso8601String(),
                'description' => $file->description,
                'sectionLabel' => $sectionLabels[$section] ?? $section,
                'grade' => $file->grade ?: StoredFileSections::subfolderFromPath($section, $file->path),
                'courseName' => $file->course?->name,
                'week' => $pedagogicalResource?->week,
                'uploadedBy' => $file->user?->name,
                'fileType' => $fileTypeLabels[$file->file_type] ?? null,
                'size' => (int) $file->size,
                'publishedAt' => $file->published_at?->toIso8601String(),
                'tag' => $section === 'Novedades' ? 'Institucional' : null,
                'localUrl' => route('public.files.show', $file->id),
                'availableFrom' => $file->available_from?->toDateString(),
                'availableUntil' => $file->available_until?->toDateString(),
            ];
        })
            ->values()
            ->all();

        if ($category !== null) {
            return $files;
        }

        $folders = [];
        if ($folder === null) {
            foreach ($subfolders as $key => $subfolder) {
                $folders[] = [
                    'id' => 'local_'.$section.'__'.rawurlencode($key),
                    'name' => $subfolder['label'],
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'localSection' => $section,
                ];
            }
        } elseif (StoredFileSections::hasCourseHierarchy($section) && $course === null) {
            foreach ($courses as $courseOption) {
                $folders[] = [
                    'id' => 'local_'.$section.'__'.rawurlencode($folder).'__course__'.$courseOption->id,
                    'name' => $courseOption->name,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'localSection' => $section,
                ];
            }
        } elseif (StoredFileSections::hasCourseHierarchy($section) && $course !== null) {
            foreach ($categories as $key => $categoryFolder) {
                $folders[] = [
                    'id' => 'local_'.$section.'__'.rawurlencode($folder).'__course__'.$course->id.'__'.$key,
                    'name' => $categoryFolder['label'],
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'localSection' => $section,
                ];
            }
        }

        return [...$folders, ...$files];
    }

    /* ──────────────────────────────────────────────────────────
       HELPER PRIVADO
    ──────────────────────────────────────────────────────────*/
    private function listFolder(string $folderId, string $cacheKey): JsonResponse
    {
        $demoMode = config('school.demo_mode');

        if ($demoMode || empty($folderId)) {
            return response()->json(['demo' => true, 'files' => []]);
        }

        $apiKey = config('school.drive.api_key');
        $maxFiles = config('school.drive.max_files', 30);

        $ttl = now()->addMinutes(15);

        $files = Cache::remember("drive_{$cacheKey}", $ttl, function () use ($folderId, $apiKey, $maxFiles) {
            $response = Http::timeout(10)->get('https://www.googleapis.com/drive/v3/files', [
                'q' => "'{$folderId}' in parents and trashed = false",
                'fields' => 'files(id,name,mimeType,modifiedTime,size,webViewLink,webContentLink)',
                'orderBy' => 'folder,name',
                'pageSize' => $maxFiles,
                'key' => $apiKey,
            ]);

            if ($response->failed()) {
                return null;
            }

            return $response->json('files', []);
        });

        if ($files === null) {
            return response()->json(['error' => 'Error al conectar con Google Drive'], 502);
        }

        return response()->json(['demo' => false, 'files' => $files]);
    }
}
