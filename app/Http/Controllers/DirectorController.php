<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Enrollment;
use App\Models\PedagogicalResource;
use App\Models\StoredFile;
use App\Models\Student;
use App\Support\StoredFileSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class DirectorController extends Controller
{
    private function ensureAcademicManager(): void
    {
        abort_unless(Auth::user()?->hasAnyRole('admin', 'director'), 403);
    }

    public function dashboard()
    {
        $this->ensureAcademicManager();

        $stats = [
            'students' => Student::count(),
            'enrollments' => Enrollment::count(),
            'courses' => Course::count(),
            'assignments' => CourseAssignment::count(),
            'resources' => StoredFile::query()
                ->whereIn('section', array_keys(StoredFileSections::submenuOptions()))
                ->count()
                + PedagogicalResource::query()
                    ->whereNotIn('path', StoredFile::query()->select('path'))
                    ->count(),
        ];

        return view('dash.director', compact('stats'));
    }

    public function resources()
    {
        $this->ensureAcademicManager();

        $resources = PedagogicalResource::query()
            ->with(['course:id,name', 'user:id,name', 'storedFile'])
            ->latest()
            ->paginate(15);
        $sectionOptions = StoredFileSections::labels(StoredFileSections::submenuOptions());
        $subfolderOptions = [];
        foreach (StoredFileSections::submenuOptions() as $section => $destination) {
            $subfolderOptions[$section] = StoredFileSections::labels(StoredFileSections::subfolders($section));
        }
        $courseHierarchySections = StoredFileSections::courseHierarchySections();
        $fileTypeOptions = StoredFileSections::labels(StoredFileSections::fileTypeSubfolders());
        $storedFiles = StoredFile::query()
            ->with(['course:id,name', 'student:id,first_name,last_name', 'user:id,name'])
            ->whereIn('section', array_keys($sectionOptions))
            ->where(function ($query): void {
                $query->where('section', '!=', 'Recursos_Pedagogicos')
                    ->orWhere('visibility', 'private');
            })
            ->latest()
            ->paginate(15, ['*'], 'files_page');
        $courses = Course::query()->orderBy('name')->get(['id', 'name']);

        return view('dash.director_resources', compact(
            'resources',
            'storedFiles',
            'courses',
            'sectionOptions',
            'subfolderOptions',
            'courseHierarchySections',
            'fileTypeOptions'
        ));
    }

    public function storeResource(Request $request)
    {
        $this->ensureAcademicManager();

        $section = (string) $request->input('section');
        $hasCourseHierarchy = StoredFileSections::hasCourseHierarchy($section);
        $isPedagogicalResource = $section === 'Recursos_Pedagogicos';
        $data = $request->validate([
            'section' => ['required', Rule::in(array_keys(StoredFileSections::submenuOptions()))],
            'subfolder' => $hasCourseHierarchy
                ? ['required', Rule::in(array_keys(StoredFileSections::subfolders($section)))]
                : ['prohibited'],
            'course_id' => $hasCourseHierarchy
                ? ['required', 'integer', Rule::exists('courses', 'id')]
                : ['prohibited'],
            'week' => $isPedagogicalResource
                ? ['required', 'string', 'max:50']
                : ['prohibited'],
            'visibility' => ['required', Rule::in(['private', 'published'])],
            'file_type' => ['required', Rule::in(['auto', 'image', 'document', 'video', 'audio', 'other'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType() ?: 'application/octet-stream';
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
        $diskName = $data['visibility'] === 'private' ? 'local' : 'public';
        $path = $file->store(
            StoredFileSections::directory(
                $section,
                $data['subfolder'] ?? null,
                $hasCourseHierarchy ? $fileType : null,
                $data['course_id'] ?? null
            ),
            $diskName
        );
        if (! $path) {
            throw new \RuntimeException('No se pudo guardar el recurso pedagógico.');
        }

        try {
            DB::transaction(function () use ($data, $diskName, $file, $fileType, $hasCourseHierarchy, $isPedagogicalResource, $mime, $path, $section): void {
                StoredFile::create([
                    'user_id' => Auth::id(),
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'disk' => $diskName,
                    'mime' => $mime,
                    'size' => $file->getSize(),
                    'visibility' => $data['visibility'],
                    'published_at' => $data['visibility'] === 'published' ? now() : null,
                    'course_id' => $hasCourseHierarchy ? $data['course_id'] : null,
                    'file_type' => $fileType,
                    'description' => filled($data['description'] ?? null)
                        ? trim($data['description'])
                        : null,
                    'section' => $section,
                ]);

                if ($isPedagogicalResource && $data['visibility'] === 'published') {
                    PedagogicalResource::create([
                        'user_id' => Auth::id(),
                        'course_id' => $data['course_id'],
                        'week' => trim($data['week']),
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime' => $mime,
                        'size' => $file->getSize(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk($diskName)->delete($path);
            throw $exception;
        }

        return redirect()->route('director.resources.index')->with('success', 'Archivo guardado en la carpeta seleccionada.');
    }

    public function destroyResource(int $id)
    {
        $this->ensureAcademicManager();

        $resource = PedagogicalResource::findOrFail($id);
        $storedFile = StoredFile::query()
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

        return redirect()->route('director.resources.index')->with('success', 'Recurso pedagógico eliminado.');
    }

    public function destroyStoredFile(int $id)
    {
        $this->ensureAcademicManager();

        $file = StoredFile::query()
            ->whereIn('section', array_keys(StoredFileSections::submenuOptions()))
            ->where(function ($query): void {
                $query->where('section', '!=', 'Recursos_Pedagogicos')
                    ->orWhere('visibility', 'private');
            })
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

        return redirect()->route('director.resources.index')->with('success', 'Archivo eliminado de su carpeta.');
    }

    public function downloadStoredFile(int $id)
    {
        $this->ensureAcademicManager();

        $file = StoredFile::query()
            ->whereIn('section', array_keys(StoredFileSections::submenuOptions()))
            ->where('visibility', 'private')
            ->findOrFail($id);
        abort_unless(
            str_starts_with($file->path, StoredFileSections::directory($file->section).'/'),
            404
        );

        $disk = Storage::disk($file->disk ?? 'local');
        abort_unless($disk->exists($file->path), 404);

        return response()->download($disk->path($file->path), $file->name);
    }
}
