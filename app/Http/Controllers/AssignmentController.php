<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    private function ensureAcademicManager()
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole('admin', 'director')) {
            abort(403);
        }

        return $user;
    }

    public function create(Request $request)
    {
        $this->ensureAcademicManager();

        $teachers = User::where('role', 'docente')->orderBy('name')->get();
        $courses = Course::orderBy('name')->get();
        $search = trim((string) $request->query('search', ''));
        $assignmentQuery = CourseAssignment::query()->with(['teacher', 'course']);

        if ($search !== '') {
            $assignmentQuery->where(function ($query) use ($search): void {
                $query->where('grade', 'like', "%{$search}%")
                    ->orWhere('section', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($teacherQuery) use ($search): void {
                        $teacherQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $assignments = $assignmentQuery->latest()->paginate(10)->withQueryString();
        $assignmentCount = CourseAssignment::count();

        return view('dash.assignments.create', compact('teachers', 'courses', 'assignments', 'assignmentCount', 'search'))
            ->with('assignment', null);
    }

    public function edit(int $id)
    {
        $this->ensureAcademicManager();

        $assignment = CourseAssignment::with(['teacher', 'course'])->findOrFail($id);
        $teachers = User::where('role', 'docente')->orderBy('name')->get();
        $courses = Course::orderBy('name')->get();
        $assignments = CourseAssignment::query()->with(['teacher', 'course'])->latest()->paginate(10);
        $assignmentCount = CourseAssignment::count();
        $search = '';

        return view('dash.assignments.create', compact('assignment', 'teachers', 'courses', 'assignments', 'assignmentCount', 'search'));
    }

    public function store(Request $request)
    {
        $this->ensureAcademicManager();

        $data = $request->validate([
            'teacher_id' => ['required', Rule::exists('users', 'id')->where('role', 'docente')],
            'course_id' => 'required|exists:courses,id',
            'grade' => ['required', Rule::in(CourseAssignment::GRADES)],
            'section' => 'nullable|string|max:60',
        ]);

        $assignment = CourseAssignment::firstOrNew([
            'course_id' => $data['course_id'],
            'user_id' => $data['teacher_id'],
        ]);
        if ($assignment->exists && filled($assignment->grade)) {
            return back()->withErrors(['course_id' => 'Este docente ya tiene asignado el curso seleccionado.']);
        }

        $assignment->grade = $data['grade'];
        $assignment->section = $data['section'] ?? null;
        $assignment->save();

        return redirect()->route('admin.assignments.create')->with(
            'success',
            $assignment->wasRecentlyCreated
                ? 'Asignación guardada correctamente.'
                : 'Se completó el grado de la asignación existente.'
        );
    }

    public function update(Request $request, int $id)
    {
        $this->ensureAcademicManager();

        $assignment = CourseAssignment::findOrFail($id);
        $data = $request->validate([
            'teacher_id' => ['required', Rule::exists('users', 'id')->where('role', 'docente')],
            'course_id' => 'required|exists:courses,id',
            'grade' => ['required', Rule::in(CourseAssignment::GRADES)],
            'section' => 'nullable|string|max:60',
        ]);

        $duplicate = CourseAssignment::where('user_id', $data['teacher_id'])
            ->where('course_id', $data['course_id'])
            ->where('id', '<>', $assignment->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['course_id' => 'Este docente ya tiene asignado el curso seleccionado.'])->withInput();
        }

        $assignment->update([
            'user_id' => $data['teacher_id'],
            'course_id' => $data['course_id'],
            'grade' => $data['grade'],
            'section' => $data['section'] ?? null,
        ]);

        return redirect()->route('admin.assignments.create')->with('success', 'Asignación actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        $this->ensureAcademicManager();

        CourseAssignment::findOrFail($id)->delete();

        return redirect()->route('admin.assignments.create')->with('success', 'Asignación eliminada correctamente.');
    }
}
