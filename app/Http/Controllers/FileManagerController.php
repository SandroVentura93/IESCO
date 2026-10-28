<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Course;
use App\Models\Student;
use App\Models\StoredFile;
use App\Support\StoredFileSections;

class FileManagerController extends Controller
{
    public function mediaLibrary()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $files = StoredFile::with('user')
            ->where('section', StoredFile::MEDIA_LIBRARY_SECTION)
            ->latest()
            ->paginate(24);

        return view('dash.media_library', compact('user', 'files'));
    }

    public function storeMediaLibraryFile(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $request->validate([
            'file' => 'required|file|max:512000',
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store(StoredFileSections::directory(StoredFile::MEDIA_LIBRARY_SECTION), 'local');
        if (!$path) {
            return back()->withErrors(['file' => 'No se pudo guardar el archivo. Inténtalo de nuevo.']);
        }

        $mime = $uploadedFile->getMimeType() ?: 'application/octet-stream';
        $fileType = match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            $mime === 'application/pdf' => 'document',
            default => 'other',
        };

        StoredFile::create([
            'user_id' => $user->id,
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => 'local',
            'mime' => $mime,
            'size' => $uploadedFile->getSize(),
            'visibility' => 'private',
            'file_type' => $fileType,
            'section' => StoredFile::MEDIA_LIBRARY_SECTION,
        ]);

        return back()->with('success', 'Archivo guardado en la biblioteca multimedia.');
    }

    public function previewMediaLibraryFile($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $file = StoredFile::where('section', StoredFile::MEDIA_LIBRARY_SECTION)->findOrFail($id);
        $disk = Storage::disk($file->disk ?? 'local');
        abort_unless($disk->exists($file->path), 404);

        $mime = $file->mime ?: 'application/octet-stream';
        if (str_starts_with($mime, 'audio/')) {
            return response()->file($disk->path($file->path), [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => 'sandbox',
            ]);
        }

        $inlineTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/webm',
            'audio/mpeg',
            'audio/ogg',
            'audio/wav',
            'audio/webm',
        ];

        if (!in_array($mime, $inlineTypes, true)) {
            return response()->download($disk->path($file->path), $file->name);
        }

        return response()->file($disk->path($file->path), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => 'sandbox',
        ]);
    }

    public function destroyMediaLibraryFile($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $file = StoredFile::where('section', StoredFile::MEDIA_LIBRARY_SECTION)->findOrFail($id);
        Storage::disk($file->disk ?? 'local')->delete($file->path);
        $file->delete();

        return back()->with('success', 'Archivo eliminado de la biblioteca multimedia.');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $section = (string) $request->input('section');
        $hasCourseHierarchy = StoredFileSections::hasCourseHierarchy($section);
        $request->validate([
            'file' => 'required|file|max:512000', // max 500MB
            'visibility' => 'required|in:private,published',
            'grade' => 'nullable|string|max:60',
            'file_type' => 'required|in:auto,image,document,video,audio,other',
            'description' => 'nullable|string|max:2000',
            'section' => ['required', Rule::in(array_keys(StoredFileSections::submenuOptions()))],
            'subfolder' => [
                $hasCourseHierarchy ? 'required' : 'nullable',
                Rule::in(array_keys(StoredFileSections::subfolders($section))),
            ],
            'course_id' => $hasCourseHierarchy
                ? ['required', 'integer', Rule::exists('courses', 'id')]
                : ['prohibited'],
            'student_id' => $section === 'Actividades_Extracurriculares'
                ? ['nullable', 'integer', Rule::exists('students', 'id')]
                : ['prohibited'],
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
        ]);

        $f = $request->file('file');
        $disk = 'local';
        $section = $request->input('section');
        $subfolder = $request->input('subfolder');
        $courseId = $request->input('course_id');
        $incomingType = $request->input('file_type');
        $mime = $f->getMimeType() ?: 'application/octet-stream';
        $effectiveType = $incomingType === 'auto' || !$incomingType ? null : $incomingType;
        if (!$effectiveType) {
            if (str_starts_with($mime, 'image/')) $effectiveType = 'image';
            elseif (str_starts_with($mime, 'video/')) $effectiveType = 'video';
            elseif (str_starts_with($mime, 'audio/')) $effectiveType = 'audio';
            elseif ($mime === 'application/octet-stream') $effectiveType = 'other';
            elseif (str_starts_with($mime, 'application/') || str_starts_with($mime, 'text/')) $effectiveType = 'document';
            else $effectiveType = 'other';
        }

        $fileTypeFolder = StoredFileSections::hasCourseHierarchy($section) && $subfolder
            ? $effectiveType
            : null;
        $path = $f->store(
            StoredFileSections::directory($section, $subfolder, $fileTypeFolder, $courseId),
            $disk
        );
        if (!$path) {
            return back()->withInput()->withErrors(['file' => 'No se pudo guardar el archivo. Inténtalo de nuevo.']);
        }

        $private = StoredFileSections::isPrivate($section);
        StoredFile::create([
            'user_id' => $user->id,
            'name' => $f->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime' => $mime,
            'size' => $f->getSize(),
            'visibility' => $private ? 'private' : $request->input('visibility'),
            'published_at' => !$private && $request->input('visibility') === 'published' ? now() : null,
            'available_from' => $private ? null : $request->input('available_from'),
            'available_until' => $private ? null : $request->input('available_until'),
            'grade' => $private ? null : $request->input('grade'),
            'student_id' => $section === 'Actividades_Extracurriculares' ? $request->input('student_id') : null,
            'course_id' => StoredFileSections::hasCourseHierarchy($section) ? $courseId : null,
            'file_type' => $effectiveType,
            'description' => $request->input('description'),
            'section' => $section,
        ]);

        return back()->with('success','Archivo subido correctamente.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $file = $this->findManagedFile($id)->load('student');
        $students = Student::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'grade']);
        $courses = StoredFileSections::courses();
        $sectionOptions = StoredFileSections::labels(StoredFileSections::publicOptions());
        $courseHierarchySections = StoredFileSections::courseHierarchySections();
        $subfolderOptions = [];
        foreach (StoredFileSections::submenuOptions() as $section => $destination) {
            $subfolderOptions[$section] = StoredFileSections::labels(StoredFileSections::subfolders($section));
        }
        $subfolder = $file->section
            ? StoredFileSections::subfolderFromPath($file->section, $file->path)
            : null;
        $courseId = $file->course_id;

        return view('dash.files_edit', compact(
            'user',
            'file',
            'students',
            'sectionOptions',
            'subfolderOptions',
            'subfolder',
            'courses',
            'courseId',
            'courseHierarchySections'
        ));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $file = $this->findManagedFile($id);
        $section = (string) $request->input('section');
        $hasCourseHierarchy = StoredFileSections::hasCourseHierarchy($section);
        $request->validate([
            'file' => 'nullable|file|max:512000',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'section' => ['nullable', Rule::in(array_keys(StoredFileSections::publicOptions()))],
            'subfolder' => ['nullable', Rule::in(array_keys(StoredFileSections::subfolders($section)))],
            'course_id' => $hasCourseHierarchy && $request->filled('subfolder')
                ? ['required', 'integer', Rule::exists('courses', 'id')]
                : ['prohibited'],
            'student_id' => $section === 'Actividades_Extracurriculares'
                ? ['nullable', 'integer', Rule::exists('students', 'id')]
                : ['prohibited'],
            'grade' => 'nullable|string|max:60',
            'file_type' => 'required|in:image,document,video,audio,other',
            'visibility' => 'required|in:private,published',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
        ]);

        $section = $request->input('section');
        $subfolder = $request->input('subfolder');
        $courseId = $request->input('course_id');
        $fileType = $request->input('file_type');
        $directory = $section
            ? StoredFileSections::directory(
                $section,
                $subfolder,
                StoredFileSections::hasCourseHierarchy($section) && $subfolder && $courseId ? $fileType : null,
                $courseId
            )
            : 'files/sin-clasificar';
        $diskName = $file->disk ?? 'local';
        $disk = Storage::disk($diskName);
        $oldPath = $file->path;

        if ($request->hasFile('file')) {
            $newPath = $request->file('file')->store($directory, $diskName);
            if (!$newPath) {
                return back()->withInput()->withErrors(['file' => 'No se pudo guardar el archivo reemplazado.']);
            }
            $file->path = $newPath;
            $file->disk = $diskName;
            $file->mime = $request->file('file')->getMimeType() ?: 'application/octet-stream';
            $file->size = $request->file('file')->getSize();
        } elseif ($disk->exists($oldPath)) {
            $newPath = $directory.'/'.basename($oldPath);
            if ($newPath !== $oldPath) {
                $baseName = pathinfo($newPath, PATHINFO_FILENAME);
                $extension = pathinfo($newPath, PATHINFO_EXTENSION);
                $counter = 1;
                while ($disk->exists($newPath)) {
                    $newPath = $directory.'/'.$baseName.'-'.$counter.($extension ? '.'.$extension : '');
                    $counter++;
                }
                if (!$disk->move($oldPath, $newPath)) {
                    return back()->withInput()->withErrors(['file' => 'No se pudo mover el archivo a su nueva carpeta.']);
                }
                $file->path = $newPath;
            }
        }

        $file->name = trim($request->input('name'));
        $file->description = $request->input('description');
        $file->section = $section;
        $file->grade = $request->input('grade');
        $file->student_id = $section === 'Actividades_Extracurriculares'
            ? $request->input('student_id')
            : null;
        $file->course_id = StoredFileSections::hasCourseHierarchy((string) $section) ? $courseId : null;
        $file->file_type = $fileType;
        $file->visibility = $request->input('visibility');
        $file->published_at = $file->visibility === 'published'
            ? ($file->published_at ?? now())
            : null;
        $file->available_from = $request->input('available_from');
        $file->available_until = $request->input('available_until');
        $file->save();

        if ($request->hasFile('file') && $oldPath !== $file->path) {
            $disk->delete($oldPath);
        }

        return redirect()->route('admin.files.panel')
            ->with('success', 'Los cambios del archivo se guardaron correctamente.');
    }

    public function updateSection(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $request->validate([
            'section' => 'nullable|in:'.implode(',', array_keys(StoredFileSections::publicOptions())),
        ]);

        $file = $this->findManagedFile($id);
        $section = $request->input('section');
        $destination = $section ? StoredFileSections::directory($section) : 'files/sin-clasificar';
        $disk = Storage::disk($file->disk ?? 'public');
        if ($disk->exists($file->path)) {
            $filename = basename($file->path);
            $newPath = $destination.'/'.$filename;
            if ($newPath !== $file->path) {
                $counter = 1;
                while ($disk->exists($newPath)) {
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $suffix = $extension === '' ? '' : '.'.$extension;
                    $newPath = $destination.'/'.pathinfo($filename, PATHINFO_FILENAME).'-'.$counter.$suffix;
                    $counter++;
                }

                if (!$disk->move($file->path, $newPath)) {
                    return back()->withErrors(['file' => 'No se pudo mover el archivo a la carpeta seleccionada.']);
                }
                $file->path = $newPath;
            }
        }
        $file->section = $section;
        $file->save();

        return back()->with('success','Sección actualizada.');
    }

    public function publish($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $file = $this->findManagedFile($id);
        $file->visibility = 'published';
        $file->published_at = now();
        $file->save();

        return back()->with('success','Archivo publicado.');
    }

    public function unpublish($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $file = $this->findManagedFile($id);
        $file->visibility = 'private';
        $file->published_at = null;
        $file->save();

        return back()->with('success','Archivo despublicado.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $file = $this->findManagedFile($id);
        Storage::disk($file->disk ?? 'public')->delete($file->path);
        $file->delete();

        return back()->with('success','Archivo eliminado.');
    }

    public function download($id)
    {
        $user = Auth::user();
        if (!$user) abort(403);

        $f = $this->findManagedFile($id);
        // Use response()->download to ensure headers and avoid lint warnings
        $disk = Storage::disk($f->disk ?? 'public');
        $abs = $disk->path($f->path);
        return response()->download($abs, $f->name);
    }

    public function preview($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') abort(403);

        $file = $this->findManagedFile($id);
        $disk = Storage::disk($file->disk ?? 'public');
        abort_unless($disk->exists($file->path), 404);

        return response()->file($disk->path($file->path), [
            'Content-Type' => $file->mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => 'sandbox',
        ]);
    }

    public function publicFile($id)
    {
        $file = StoredFile::publiclyAvailable()
            ->where(function ($query) {
                $query->whereNull('section')
                    ->orWhere('section', '!=', StoredFile::MEDIA_LIBRARY_SECTION);
            })
            ->findOrFail($id);
        $disk = Storage::disk($file->disk ?? 'public');
        abort_unless($disk->exists($file->path), 404);

        $mime = $file->mime ?: 'application/octet-stream';
        if (str_starts_with($mime, 'audio/')) {
            return response()->file($disk->path($file->path), [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => 'sandbox',
            ]);
        }

        $inlineTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/webm',
        ];

        if (!in_array($mime, $inlineTypes, true)) {
            return response()->download($disk->path($file->path), $file->name);
        }

        return response()->file($disk->path($file->path), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => 'sandbox',
        ]);
    }

    private function findManagedFile($id): StoredFile
    {
        $file = StoredFile::findOrFail($id);
        abort_if($file->section === StoredFile::MEDIA_LIBRARY_SECTION, 404);

        return $file;
    }
}
