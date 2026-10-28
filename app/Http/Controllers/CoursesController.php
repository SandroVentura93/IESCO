<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\PedagogicalResource;
use App\Models\StoredFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole('admin', 'director')) {
            abort(403);
        }

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['q'] ?? '');
        $query = Course::query()
            ->with('teachers:id,name')
            ->withCount(['teachers', 'pedagogicalResources', 'storedFiles']);
        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $courses = $query->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();
        $stats = [
            'courses' => Course::count(),
            'assignments' => CourseAssignment::count(),
            'resources' => PedagogicalResource::count() + StoredFile::whereNotNull('course_id')->count(),
            'unassigned' => Course::doesntHave('teachers')->count(),
        ];

        return view('dash.courses.index', compact('courses', 'search', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole('admin', 'director')) {
            abort(403);
        }

        return view('dash.courses.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole('admin', 'director')) {
            abort(403);
        }
        if (is_string($request->input('name'))) {
            $request->merge(['name' => trim($request->input('name'))]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        Course::create($data);

        return redirect()->route('admin.courses.index')->with('success', 'Curso creado correctamente.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole('admin', 'director')) {
            abort(403);
        }
        $c = Course::findOrFail($id);

        return view('dash.courses.edit', compact('c'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole('admin', 'director')) {
            abort(403);
        }
        $c = Course::findOrFail($id);
        if (is_string($request->input('name'))) {
            $request->merge(['name' => trim($request->input('name'))]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $c->update($data);

        return redirect()->route('admin.courses.index')->with('success', 'Curso actualizado.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole('admin', 'director')) {
            abort(403);
        }
        $c = Course::findOrFail($id);
        if (
            $c->storedFiles()->exists()
            || $c->pedagogicalResources()->exists()
            || $c->teachers()->exists()
        ) {
            return back()->withErrors([
                'course' => 'No se puede eliminar un curso con docentes asignados o recursos asociados. Quita primero esas relaciones.',
            ]);
        }
        $c->delete();

        return back()->with('success', 'Curso eliminado.');
    }
}
