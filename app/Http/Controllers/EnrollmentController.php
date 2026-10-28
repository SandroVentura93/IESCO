<?php

namespace App\Http\Controllers;

use App\Models\CourseAssignment;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    private function ensureAcademicManager(): void
    {
        abort_unless(Auth::user()?->hasAnyRole('admin', 'director'), 403);
    }

    public function index(Request $request)
    {
        $this->ensureAcademicManager();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['nullable', 'integer', 'between:2000,2100'],
            'grade' => ['nullable', 'string', Rule::in(CourseAssignment::GRADES)],
            'status' => ['nullable', Rule::in(array_keys(Enrollment::STATUSES))],
        ]);
        $search = trim($filters['q'] ?? '');
        $academicYear = (int) ($filters['academic_year'] ?? now()->year);
        $grade = $filters['grade'] ?? '';
        $status = $filters['status'] ?? '';

        $query = Enrollment::query()
            ->with('student:id,dni,first_name,last_name,birth_date,user_id')
            ->where('academic_year', $academicYear);

        if ($search !== '') {
            $query->where(function ($enrollmentQuery) use ($search): void {
                $enrollmentQuery->where('enrollment_code', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search): void {
                        $studentQuery->where('dni', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($grade !== '') {
            $query->where('grade', $grade);
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        $enrollments = $query->orderByDesc('enrollment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Enrollment::where('academic_year', $academicYear)->count(),
            'draft' => Enrollment::where('academic_year', $academicYear)->where('status', 'draft')->count(),
            'enrolled' => Enrollment::where('academic_year', $academicYear)->where('status', 'enrolled')->count(),
            'cancelled' => Enrollment::where('academic_year', $academicYear)->where('status', 'cancelled')->count(),
        ];

        return view('dash.enrollments.index', compact('enrollments', 'search', 'academicYear', 'grade', 'status', 'stats'));
    }

    public function create(Request $request)
    {
        $this->ensureAcademicManager();

        $filters = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'academic_year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $academicYear = (int) ($filters['academic_year'] ?? now()->year);
        $selectedStudentId = $filters['student_id'] ?? null;
        $students = Student::query()
            ->whereDoesntHave('enrollments', fn ($query) => $query->where('academic_year', $academicYear))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'dni', 'first_name', 'last_name', 'birth_date', 'grade']);

        if ($selectedStudentId && ! $students->contains('id', (int) $selectedStudentId)) {
            abort(404);
        }

        return view('dash.enrollments.create', compact('students', 'academicYear', 'selectedStudentId'));
    }

    public function store(Request $request)
    {
        $this->ensureAcademicManager();
        $data = $this->validatedEnrollmentData($request);

        $enrollment = DB::transaction(function () use ($data): Enrollment {
            $enrollment = Enrollment::create($data + ['status' => 'draft']);
            $enrollment->enrollment_code = sprintf('MAT-%d-%06d', $enrollment->academic_year, $enrollment->id);
            $enrollment->save();

            return $enrollment;
        });

        return redirect()->route('admin.enrollments.ficha', $enrollment->id)
            ->with('success', 'Matrícula creada como borrador. Revisa la ficha y confirma el registro cuando esté lista.');
    }

    public function storeStudent(Request $request): JsonResponse
    {
        $this->ensureAcademicManager();
        $request->merge([
            'dni' => is_string($request->input('dni')) ? mb_strtoupper(trim($request->input('dni')), 'UTF-8') : $request->input('dni'),
            'first_name' => is_string($request->input('first_name')) ? mb_strtoupper(trim($request->input('first_name')), 'UTF-8') : $request->input('first_name'),
            'last_name' => is_string($request->input('last_name')) ? mb_strtoupper(trim($request->input('last_name')), 'UTF-8') : $request->input('last_name'),
            'grade' => is_string($request->input('grade')) ? trim($request->input('grade')) : $request->input('grade'),
        ]);

        $validator = Validator::make($request->all(), [
            'dni' => ['required', 'string', 'max:50', 'unique:students,dni'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'grade' => ['nullable', 'string', Rule::in(CourseAssignment::GRADES)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa los datos del alumno.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $student = Student::create($data);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'dni' => $student->dni,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'grade' => $student->grade,
            ],
            'message' => 'Alumno agregado correctamente. Ya está seleccionado para esta matrícula.',
        ], 201);
    }

    public function edit($id)
    {
        $this->ensureAcademicManager();
        $enrollment = Enrollment::with('student')->findOrFail($id);
        abort_if($enrollment->status === 'cancelled', 403);

        return view('dash.enrollments.edit', compact('enrollment'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureAcademicManager();
        $enrollment = Enrollment::findOrFail($id);
        abort_if($enrollment->status === 'cancelled', 403);
        $data = $this->validatedEnrollmentData($request, $enrollment);
        unset($data['student_id']);
        $enrollment->update($data);

        return redirect()->route('admin.enrollments.ficha', $enrollment->id)
            ->with('success', 'Datos de matrícula actualizados.');
    }

    public function ficha($id)
    {
        $this->ensureAcademicManager();
        $enrollment = Enrollment::with('student.user')->findOrFail($id);

        return view('dash.enrollments.ficha', compact('enrollment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $this->ensureAcademicManager();
        $data = $request->validate([
            'status' => ['required', Rule::in(['enrolled', 'cancelled'])],
        ]);
        $enrollment = Enrollment::findOrFail($id);

        if ($enrollment->status === 'cancelled') {
            return back()->withErrors(['status' => 'Una matrícula anulada no puede modificarse.']);
        }
        if ($enrollment->status === $data['status']) {
            return back()->withErrors(['status' => 'La matrícula ya tiene ese estado.']);
        }

        $enrollment->status = $data['status'];
        $enrollment->cancelled_at = $data['status'] === 'cancelled' ? now() : null;
        $enrollment->save();

        $message = $data['status'] === 'enrolled'
            ? 'Matrícula confirmada correctamente.'
            : 'Matrícula anulada. El registro se conserva en el historial.';

        return back()->with('success', $message);
    }

    private function validatedEnrollmentData(Request $request, ?Enrollment $enrollment = null): array
    {
        $request->merge([
            'section' => is_string($request->input('section')) ? mb_strtoupper(trim($request->input('section')), 'UTF-8') : $request->input('section'),
            'guardian_name' => is_string($request->input('guardian_name')) ? mb_strtoupper(trim($request->input('guardian_name')), 'UTF-8') : $request->input('guardian_name'),
            'guardian_relationship' => is_string($request->input('guardian_relationship')) ? trim($request->input('guardian_relationship')) : $request->input('guardian_relationship'),
            'guardian_phone' => is_string($request->input('guardian_phone')) ? trim($request->input('guardian_phone')) : $request->input('guardian_phone'),
            'guardian_email' => is_string($request->input('guardian_email')) ? mb_strtolower(trim($request->input('guardian_email')), 'UTF-8') : $request->input('guardian_email'),
            'address' => is_string($request->input('address')) ? trim($request->input('address')) : $request->input('address'),
            'notes' => is_string($request->input('notes')) ? trim($request->input('notes')) : $request->input('notes'),
        ]);

        $academicYear = $request->input('academic_year');
        $uniqueStudentYear = Rule::unique('enrollments', 'student_id')
            ->where(fn ($query) => $query->where('academic_year', $academicYear));
        if ($enrollment) {
            $uniqueStudentYear->ignore($enrollment->id);
        }

        return $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id', $uniqueStudentYear],
            'academic_year' => ['required', 'integer', 'between:2000,2100'],
            'enrollment_date' => ['required', 'date', 'before_or_equal:today'],
            'grade' => ['required', 'string', Rule::in(CourseAssignment::GRADES)],
            'section' => ['nullable', 'string', 'max:20'],
            'guardian_name' => ['required', 'string', 'max:180'],
            'guardian_relationship' => ['required', Rule::in(Enrollment::GUARDIAN_RELATIONSHIPS)],
            'guardian_phone' => ['nullable', 'string', 'max:40'],
            'guardian_email' => ['nullable', 'email', 'max:180'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
