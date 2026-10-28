<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StoredFile;
use App\Models\Student;
use App\Models\User;
use App\Support\StoredFileSections;

class DashboardController extends Controller
{
    private function ensureAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        return $user;
    }

    public function admin()
    {
        $user = $this->ensureAdmin();

        $stats = [
            'users' => User::count(),
            'files' => StoredFile::count(),
            'published' => StoredFile::where('visibility','published')->count(),
        ];

        return view('dash.admin', compact('user','stats'));
    }

    public function filesPanel()
    {
        $user = $this->ensureAdmin();

        $filesQuery = StoredFile::with(['user', 'student'])
            ->where(function ($query) {
                $query->whereNull('section')
                    ->orWhere('section', '!=', StoredFile::MEDIA_LIBRARY_SECTION);
            });
        if (request()->filled('q')) {
            $search = trim((string) request('q'));
            $filesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }
        if (request()->filled('visibility') && in_array(request('visibility'), ['private', 'published'], true)) {
            $filesQuery->where('visibility', request('visibility'));
        }
        $files = $filesQuery->latest()->paginate(15)->withQueryString();
        $managedFiles = static function () {
            return StoredFile::where(function ($query) {
                $query->whereNull('section')
                    ->orWhere('section', '!=', StoredFile::MEDIA_LIBRARY_SECTION);
            });
        };
        $students = Student::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'grade']);
        $fileStats = [
            'total' => $managedFiles()->count(),
            'published' => $managedFiles()->where('visibility', 'published')->count(),
            'private' => $managedFiles()->where('visibility', 'private')->count(),
        ];
        $sectionOptions = StoredFileSections::labels(StoredFileSections::publicOptions());
        $projectDestinationOptions = StoredFileSections::labels(StoredFileSections::submenuOptions());
        $subfolderOptions = [];
        foreach (StoredFileSections::submenuOptions() as $section => $destination) {
            $subfolderOptions[$section] = StoredFileSections::labels(StoredFileSections::subfolders($section));
        }
        $courses = StoredFileSections::courses();
        $courseHierarchySections = StoredFileSections::courseHierarchySections();

        return view('dash.files_panel', compact(
            'user',
            'files',
            'fileStats',
            'students',
            'sectionOptions',
            'projectDestinationOptions',
            'subfolderOptions',
            'courses',
            'courseHierarchySections'
        ));
    }

    public function usersMaintenance()
    {
        $user = $this->ensureAdmin();

        $users = User::latest()->paginate(15);

        return view('dash.users_maintenance', compact('user', 'users'));
    }

    public function teacher()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'docente') abort(403);
        return view('dash.teacher', compact('user'));
    }

    public function student()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'alumno') abort(403);
        return view('dash.student', compact('user'));
    }
}
