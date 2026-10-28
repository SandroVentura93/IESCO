<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_form_guides_admin_to_create_a_student_when_none_are_available(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.enrollments.create'))
            ->assertOk()
            ->assertSee('No hay alumnos disponibles para matricular este año')
            ->assertSee(route('admin.students.create'), false);
    }

    public function test_enrollment_student_dropdown_includes_a_searchable_student_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Student::create([
            'dni' => '12345678',
            'first_name' => 'ANA',
            'last_name' => 'GARCÍA',
            'grade' => '1°',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.enrollments.create'))
            ->assertOk()
            ->assertSee('role="combobox"', false)
            ->assertSee('role="listbox"', false)
            ->assertSee('Buscar alumno por nombre, apellido o DNI', false)
            ->assertSee('data-open-student-modal', false)
            ->assertSee('aria-labelledby="addStudentModalTitle"', false)
            ->assertSee('Guardar y seleccionar')
            ->assertSee('data-student-dni="12345678"', false)
            ->assertSee('GARCÍA, ANA');
    }

    public function test_admin_can_add_a_student_from_the_enrollment_modal_and_duplicate_documents_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.enrollments.students.store'), [
                'dni' => ' ab123456 ',
                'first_name' => ' ana maría ',
                'last_name' => ' pérez ',
                'birth_date' => '2014-09-03',
                'grade' => '2°',
            ]);

        $response->assertCreated()
            ->assertJsonPath('student.dni', 'AB123456')
            ->assertJsonPath('student.first_name', 'ANA MARÍA')
            ->assertJsonPath('student.last_name', 'PÉREZ')
            ->assertJsonPath('student.grade', '2°');

        $this->assertDatabaseHas('students', [
            'dni' => 'AB123456',
            'first_name' => 'ANA MARÍA',
            'last_name' => 'PÉREZ',
            'grade' => '2°',
        ]);
        $this->assertSame(
            '2014-09-03',
            Student::where('dni', 'AB123456')->firstOrFail()->birth_date->toDateString()
        );

        $this->postJson(route('admin.enrollments.students.store'), [
            'dni' => 'AB123456',
            'first_name' => 'Otra',
            'last_name' => 'Persona',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['dni']]);

        $this->assertDatabaseCount('students', 1);
    }

    public function test_admin_can_create_a_draft_and_print_its_enrollment_sheet(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'dni' => '12345678',
            'first_name' => 'ANA',
            'last_name' => 'GARCÍA',
            'birth_date' => '2015-04-10',
            'grade' => '1°',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.enrollments.store'), [
            'student_id' => $student->id,
            'academic_year' => now()->year,
            'enrollment_date' => now()->toDateString(),
            'grade' => '1°',
            'section' => 'a',
            'guardian_name' => 'María García',
            'guardian_relationship' => 'Madre',
            'guardian_phone' => '999 111 222',
            'guardian_email' => 'MARIA@example.test',
            'address' => 'Av. Central 123',
            'notes' => 'Documentación revisada.',
        ]);

        $enrollment = Enrollment::firstOrFail();
        $response->assertRedirect(route('admin.enrollments.ficha', $enrollment->id))
            ->assertSessionHas('success');

        $this->assertSame('draft', $enrollment->status);
        $this->assertSame(sprintf('MAT-%d-%06d', now()->year, $enrollment->id), $enrollment->enrollment_code);
        $this->assertSame('A', $enrollment->section);
        $this->assertSame('MARÍA GARCÍA', $enrollment->guardian_name);
        $this->assertSame('maria@example.test', $enrollment->guardian_email);

        $this->get(route('admin.enrollments.ficha', $enrollment->id))
            ->assertOk()
            ->assertSee('Ficha de matrícula')
            ->assertSee($enrollment->enrollment_code)
            ->assertSee('ANA')
            ->assertSee('Firma del apoderado')
            ->assertSee('Imprimir ficha');

        $this->get(route('admin.enrollments.index', [
            'q' => $student->dni,
            'academic_year' => now()->year,
            'grade' => '1°',
            'status' => 'draft',
        ]))
            ->assertOk()
            ->assertSee($enrollment->enrollment_code)
            ->assertSee('Borradores')
            ->assertSee('ANA');
    }

    public function test_student_can_only_be_enrolled_once_per_academic_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'dni' => '87654321',
            'first_name' => 'LUIS',
            'last_name' => 'PÉREZ',
        ]);
        $enrollmentData = [
            'student_id' => $student->id,
            'academic_year' => now()->year,
            'enrollment_date' => now()->toDateString(),
            'grade' => '2°',
            'guardian_name' => 'Carlos Pérez',
            'guardian_relationship' => 'Padre',
        ];

        $this->actingAs($admin)
            ->post(route('admin.enrollments.store'), $enrollmentData)
            ->assertRedirect();

        $this->post(route('admin.enrollments.store'), $enrollmentData)
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_admin_can_edit_an_enrollment_without_changing_its_student_or_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'dni' => '55667788',
            'first_name' => 'ELENA',
            'last_name' => 'TORRES',
        ]);
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'enrollment_code' => 'MAT-'.now()->year.'-000002',
            'academic_year' => now()->year,
            'enrollment_date' => now()->toDateString(),
            'grade' => '1°',
            'guardian_name' => 'Rosa Torres',
            'guardian_relationship' => 'Madre',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.enrollments.update', $enrollment->id), [
                'student_id' => $student->id,
                'academic_year' => now()->year,
                'enrollment_date' => now()->toDateString(),
                'grade' => '2°',
                'section' => 'b',
                'guardian_name' => 'Marta Torres',
                'guardian_relationship' => 'Tutor',
                'guardian_phone' => '999000111',
            ])
            ->assertRedirect(route('admin.enrollments.ficha', $enrollment->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'student_id' => $student->id,
            'enrollment_code' => $enrollment->enrollment_code,
            'grade' => '2°',
            'section' => 'B',
            'guardian_name' => 'MARTA TORRES',
            'guardian_relationship' => 'Tutor',
        ]);
    }

    public function test_admin_can_confirm_and_cancel_an_enrollment_and_cancelled_records_are_read_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'dni' => '11223344',
            'first_name' => 'SOFÍA',
            'last_name' => 'RAMÍREZ',
        ]);
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'enrollment_code' => 'MAT-'.now()->year.'-000001',
            'academic_year' => now()->year,
            'enrollment_date' => now()->toDateString(),
            'grade' => '3°',
            'guardian_name' => 'Ana Ramírez',
            'guardian_relationship' => 'Madre',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.enrollments.status', $enrollment->id), ['status' => 'enrolled'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'status' => 'enrolled']);

        $this->patch(route('admin.enrollments.status', $enrollment->id), ['status' => 'cancelled'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'cancelled',
        ]);
        $this->assertNotNull($enrollment->fresh()->cancelled_at);

        $this->get(route('admin.enrollments.edit', $enrollment->id))->assertForbidden();
        $this->put(route('admin.enrollments.update', $enrollment->id), [])->assertForbidden();
    }

    public function test_enrollment_management_is_restricted_to_admins(): void
    {
        $studentUser = User::factory()->create(['role' => 'alumno']);

        $this->actingAs($studentUser)
            ->get(route('admin.enrollments.index'))
            ->assertForbidden();

        $this->get(route('admin.enrollments.create'))->assertForbidden();

        $this->postJson(route('admin.enrollments.students.store'), [
            'dni' => '12345678',
            'first_name' => 'Ana',
            'last_name' => 'García',
        ])->assertForbidden();
    }
}
