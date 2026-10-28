<?php

namespace App\Http\Controllers;

use App\Models\CourseAssignment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    private function ensureAcademicManager()
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole('admin', 'director')) {
            abort(403);
        }

        return $user;
    }

    public function index(Request $request)
    {
        $this->ensureAcademicManager();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'grade' => ['nullable', 'string', 'max:60'],
        ]);
        $search = trim($filters['q'] ?? '');
        $grade = trim($filters['grade'] ?? '');
        $query = Student::query();

        if ($search !== '') {
            $query->where(function ($studentQuery) use ($search): void {
                $studentQuery->where('dni', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('grade', 'like', "%{$search}%");
            });
        }

        if ($grade !== '') {
            $query->where('grade', $grade);
        }

        $students = $query->with('user:id,name,email')
            ->withCount([
                'enrollments as current_year_enrollments_count' => fn ($enrollmentQuery) => $enrollmentQuery->where('academic_year', now()->year),
            ])
            ->orderBy('last_name')->orderBy('first_name')->orderBy('id')
            ->paginate(15)
            ->withQueryString();
        $pendingStudentUsers = User::query()
            ->where('role', 'alumno')
            ->whereDoesntHave('studentProfile')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $grades = Student::query()
            ->whereNotNull('grade')
            ->where('grade', '<>', '')
            ->distinct()
            ->orderBy('grade')
            ->pluck('grade');
        $stats = [
            'total' => Student::count(),
            'grades' => Student::whereNotNull('grade')->where('grade', '<>', '')->distinct()->count('grade'),
            'without_grade' => Student::whereNull('grade')->orWhere('grade', '')->count(),
            'pending_accounts' => $pendingStudentUsers->count(),
        ];

        return view('dash.students.index', compact('students', 'search', 'grade', 'grades', 'stats', 'pendingStudentUsers'));
    }

    public function create(Request $request)
    {
        $this->ensureAcademicManager();

        $filters = $request->validate([
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'alumno')),
            ],
        ]);
        $selectedUserId = $filters['user_id'] ?? null;
        $studentUsers = User::query()
            ->where('role', 'alumno')
            ->whereDoesntHave('studentProfile')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        if ($selectedUserId && ! $studentUsers->contains('id', (int) $selectedUserId)) {
            abort(404);
        }

        return view('dash.students.create', compact('studentUsers', 'selectedUserId'));
    }

    public function store(Request $request)
    {
        $this->ensureAcademicManager();
        $this->trimStudentInput($request);
        $data = $request->validate([
            'dni' => 'required|string|max:50|unique:students,dni',
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'grade' => ['nullable', 'string', Rule::in(CourseAssignment::GRADES)],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'alumno')),
                Rule::unique('students', 'user_id'),
            ],
        ]);

        $student = Student::create($data);

        if ($student->user_id) {
            return redirect()->route('admin.enrollments.create', ['student_id' => $student->id])
                ->with('success', 'Ficha del alumno creada. Completa los datos para matricularlo.');
        }

        return redirect()->route('admin.students.index')->with('success', 'Alumno creado.');
    }

    public function edit($id)
    {
        $this->ensureAcademicManager();
        $s = Student::findOrFail($id);
        $studentUsers = User::query()
            ->where('role', 'alumno')
            ->where(function ($query) use ($s): void {
                $query->whereDoesntHave('studentProfile');
                if ($s->user_id) {
                    $query->orWhereKey($s->user_id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('dash.students.edit', compact('s', 'studentUsers'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureAcademicManager();
        $s = Student::findOrFail($id);
        $this->trimStudentInput($request);
        $data = $request->validate([
            'dni' => ['required', 'string', 'max:50', Rule::unique('students', 'dni')->ignore($s->id)],
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'grade' => ['nullable', 'string', Rule::in(CourseAssignment::GRADES)],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'alumno')),
                Rule::unique('students', 'user_id')->ignore($s->id),
            ],
        ]);
        $s->update($data);

        return redirect()->route('admin.students.index')->with('success', 'Alumno actualizado.');
    }

    private function trimStudentInput(Request $request): void
    {
        $normalized = [];
        foreach (['dni', 'first_name', 'last_name'] as $field) {
            $value = $request->input($field);
            $normalized[$field] = is_string($value) ? mb_strtoupper(trim($value), 'UTF-8') : $value;
        }

        $grade = $request->input('grade');
        $normalized['grade'] = is_string($grade) ? trim($grade) : $grade;

        $request->merge($normalized);
    }

    public function destroy($id)
    {
        $this->ensureAcademicManager();
        $s = Student::findOrFail($id);
        if ($s->enrollments()->exists()) {
            return back()->withErrors(['student' => 'No se puede eliminar este alumno porque tiene matrículas registradas.']);
        }
        $s->delete();

        return back()->with('success', 'Alumno eliminado.');
    }
}
