<?php

namespace App\Http\Controllers;

use App\Models\CourseAssignment;
use App\Models\PedagogicalResource;
use App\Models\StoredFile;
use App\Support\StoredFileSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class PedagogyController extends Controller
{
    public function index()
    {
        return $this->renderDashboard('overview');
    }

    public function courses()
    {
        return $this->renderDashboard('courses');
    }

    public function resources()
    {
        return $this->renderDashboard('resources');
    }

    public function createResource()
    {
        return $this->renderDashboard('upload');
    }

    private function renderDashboard(string $activePage)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'docente') {
            abort(403);
        }

        $sectionOptions = StoredFileSections::labels(StoredFileSections::submenuOptions());
        $sectionKeys = array_keys($sectionOptions);
        $subfolderOptions = [];
        foreach ($sectionKeys as $section) {
            $subfolderOptions[$section] = StoredFileSections::labels(
                StoredFileSections::subfolders($section)
            );
        }

        $courses = $user->courses()
            ->withCount([
                'storedFiles as teacher_resources_count' => static fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->whereIn('section', $sectionKeys),
            ])
            ->orderBy('name')
            ->get();
        $resources = $user->pedagogicalResources()
            ->with(['course', 'storedFile'])
            ->latest()
            ->paginate(12, ['*'], 'resources_page');
        $otherFiles = StoredFile::query()
            ->with('course')
            ->where('user_id', $user->id)
            ->whereIn('section', $sectionKeys)
            ->where('section', '!=', 'Recursos_Pedagogicos')
            ->latest()
            ->paginate(12, ['*'], 'other_files_page');
        $fileTypeOptions = StoredFileSections::labels(StoredFileSections::fileTypeSubfolders());
        $courseHierarchySections = StoredFileSections::courseHierarchySections();
        $stats = [
            'courses' => $courses->count(),
            'resources' => StoredFile::query()
                ->where('user_id', $user->id)
                ->whereIn('section', $sectionKeys)
                ->count(),
            'monthly_resources' => StoredFile::query()
                ->where('user_id', $user->id)
                ->whereIn('section', $sectionKeys)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];

        return view('dash.teacher', compact(
            'activePage',
            'user',
            'courses',
            'resources',
            'otherFiles',
            'sectionOptions',
            'subfolderOptions',
            'courseHierarchySections',
            'fileTypeOptions',
            'stats'
        ));
    }

    public function assignCourse(Request $request)
    {
        // Only admin can assign courses to teachers
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'teacher_id' => ['required', Rule::exists('users', 'id')->where('role', 'docente')],
            'course_id' => 'required|exists:courses,id',
            'grade' => ['required', Rule::in(CourseAssignment::GRADES)],
            'section' => 'nullable|string|max:60',
        ]);

        $assignment = CourseAssignment::firstOrNew([
            'user_id' => $data['teacher_id'],
            'course_id' => $data['course_id'],
        ]);

        if (! $assignment->exists || blank($assignment->grade)) {
            $assignment->grade = $data['grade'];
            $assignment->section = $data['section'] ?? null;
            $assignment->save();
        }

        return back()->with('success', 'Curso asignado al docente.');
    }

    public function uploadResource(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'docente') {
            abort(403);
        }

        $section = (string) $request->input('section');
        $availableSections = array_keys(StoredFileSections::submenuOptions());
        $hasCourseHierarchy = StoredFileSections::hasCourseHierarchy($section);
        $isPedagogicalResource = $section === 'Recursos_Pedagogicos';
        $data = $request->validate([
            'section' => ['required', Rule::in($availableSections)],
            'course_id' => [
                $hasCourseHierarchy ? 'required' : 'prohibited',
                ...($hasCourseHierarchy
                    ? [
                        'integer',
                        Rule::exists('course_teacher_assignments', 'course_id')->where('user_id', $user->id),
                    ]
                    : []),
            ],
            'subfolder' => [
                $hasCourseHierarchy ? 'required' : 'prohibited',
                Rule::in(array_keys(StoredFileSections::subfolders($section))),
            ],
            'file_type' => ['required', Rule::in(['auto', 'image', 'document', 'video', 'audio', 'other'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after_or_equal:available_from'],
            'week' => $isPedagogicalResource
                ? ['required', 'string', 'max:50']
                : ['prohibited'],
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $f = $request->file('file');
        $mime = $f->getMimeType() ?: 'application/octet-stream';
        if (! in_array($mime, PedagogicalResource::ALLOWED_MIME_TYPES, true)) {
            return back()->withErrors(['file' => 'Tipo de archivo no permitido.'])->withInput();
        }

        $fileType = $data['file_type'];
        if ($fileType === 'auto') {
            $fileType = match (true) {
                str_starts_with($mime, 'image/') => 'image',
                str_starts_with($mime, 'video/') => 'video',
                str_starts_with($mime, 'audio/') => 'audio',
                str_starts_with($mime, 'application/') => 'document',
                default => 'other',
            };
        }

        $path = $f->store(
            StoredFileSections::directory(
                $section,
                $data['subfolder'] ?? null,
                $hasCourseHierarchy ? $fileType : null,
                $data['course_id'] ?? null
            ),
            'public'
        );
        if (! $path) {
            throw new \RuntimeException('No se pudo guardar el recurso pedagógico.');
        }

        try {
            DB::transaction(function () use ($data, $f, $fileType, $hasCourseHierarchy, $isPedagogicalResource, $mime, $path, $section, $user): void {
                StoredFile::create([
                    'user_id' => $user->id,
                    'name' => $f->getClientOriginalName(),
                    'path' => $path,
                    'disk' => 'public',
                    'mime' => $mime,
                    'size' => $f->getSize(),
                    'visibility' => 'published',
                    'published_at' => now(),
                    'available_from' => $data['available_from'] ?? null,
                    'available_until' => $data['available_until'] ?? null,
                    'course_id' => $hasCourseHierarchy ? $data['course_id'] : null,
                    'file_type' => $fileType,
                    'description' => filled($data['description'] ?? null)
                        ? trim($data['description'])
                        : null,
                    'section' => $section,
                ]);

                if ($isPedagogicalResource) {
                    PedagogicalResource::create([
                        'user_id' => $user->id,
                        'course_id' => $data['course_id'],
                        'week' => trim($data['week']),
                        'name' => $f->getClientOriginalName(),
                        'path' => $path,
                        'mime' => $mime,
                        'size' => $f->getSize(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        return redirect()->route('teacher.resources.index')->with('success', 'Archivo guardado en la carpeta seleccionada.');
    }

    public function destroyResource(int $id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'docente') {
            abort(403);
        }

        $resource = $user->pedagogicalResources()->findOrFail($id);
        $storedFile = StoredFile::query()
            ->where('user_id', $user->id)
            ->where('section', 'Recursos_Pedagogicos')
            ->where('path', $resource->path)
            ->first();
        $disk = Storage::disk($storedFile?->disk ?? 'public');
        if ($disk->exists($resource->path) && ! $disk->delete($resource->path)) {
            return back()->withErrors(['resource' => 'No se pudo eliminar el archivo. Inténtalo nuevamente.']);
        }

        DB::transaction(function () use ($resource, $storedFile): void {
            $storedFile?->delete();
            $resource->delete();
        });

        return redirect()->route('teacher.resources.index')->with('success', 'Tu recurso pedagógico fue eliminado.');
    }

    public function destroyStoredFile(int $id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'docente') {
            abort(403);
        }

        $allowedSections = array_keys(StoredFileSections::submenuOptions());
        $file = StoredFile::query()
            ->where('user_id', $user->id)
            ->whereIn('section', $allowedSections)
            ->where('section', '!=', 'Recursos_Pedagogicos')
            ->findOrFail($id);

        abort_unless(
            str_starts_with($file->path, StoredFileSections::directory($file->section).'/'),
            404
        );

        $disk = Storage::disk($file->disk ?? 'public');
        if ($disk->exists($file->path) && ! $disk->delete($file->path)) {
            return back()->withErrors(['file' => 'No se pudo eliminar el archivo. Inténtalo nuevamente.']);
        }

        $file->delete();

        return redirect()->route('teacher.resources.index')->with('success', 'Tu archivo fue eliminado.');
    }
}
