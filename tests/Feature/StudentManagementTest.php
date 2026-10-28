<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_student_directory_shows_summary_search_and_grade_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Student::create([
            'dni' => '12345678',
            'first_name' => 'Ana',
            'last_name' => 'García',
            'grade' => '1°',
        ]);
        Student::create([
            'dni' => '87654321',
            'first_name' => 'Luis',
            'last_name' => 'Pérez',
            'grade' => '2°',
        ]);
        Student::create([
            'dni' => '11112222',
            'first_name' => 'Sin',
            'last_name' => 'Grado',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['q' => 'ANA', 'grade' => '1°']))
            ->assertOk()
            ->assertSee('Directorio de alumnos')
            ->assertSee('Fichas de matrícula')
            ->assertSee('Grados registrados')
            ->assertSee('Ana')
            ->assertSee('12345678')
            ->assertSee('Sin grado asignado')
            ->assertSee('>1</strong>', false)
            ->assertDontSee('Luis')
            ->assertDontSee('11112222')
            ->assertSee('name="q" value="ANA"', false)
            ->assertSee('value="1°" selected', false);
    }

    public function test_student_accounts_without_a_profile_can_be_completed_and_linked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentAccount = User::factory()->create([
            'name' => 'Alumno Pendiente',
            'email' => 'pendiente@example.test',
            'role' => 'alumno',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Cuentas de alumno pendientes de ficha')
            ->assertSee('Alumno Pendiente')
            ->assertSee('pendiente@example.test')
            ->assertSee(route('admin.students.create', ['user_id' => $studentAccount->id]), false);

        $this->get(route('admin.students.create', ['user_id' => $studentAccount->id]))
            ->assertOk()
            ->assertSee('Cuenta de acceso')
            ->assertSee('value="'.$studentAccount->id.'" selected', false);

        $this->post(route('admin.students.store'), [
            'user_id' => $studentAccount->id,
            'dni' => 'ab123456',
            'first_name' => 'Ana',
            'last_name' => 'García',
            'grade' => '1°',
        ])->assertRedirect(route('admin.enrollments.create', ['student_id' => Student::where('user_id', $studentAccount->id)->value('id')]))
            ->assertSessionHas('success');

        $profile = Student::where('user_id', $studentAccount->id)->firstOrFail();
        $this->assertSame('AB123456', $profile->dni);
        $this->assertSame('ANA', $profile->first_name);
        $this->assertSame('GARCÍA', $profile->last_name);
        $this->assertSame('1°', $profile->grade);
        $this->assertSame($profile->id, $studentAccount->fresh()->studentProfile->id);

        $this->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('GARCÍA, ANA')
            ->assertSee('pendiente@example.test')
            ->assertDontSee('Cuentas de alumno pendientes de ficha');
    }

    public function test_admin_can_create_update_and_delete_a_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.students.create'))
            ->assertOk()
            ->assertSee('Registrar alumno')
            ->assertSee('name="dni"', false)
            ->assertSee('Campos obligatorios')
            ->assertSee('name="grade"', false)
            ->assertSee('Selecciona un grado')
            ->assertSee('value="1°"', false)
            ->assertSee('value="6°"', false)
            ->assertSee('value="Otro"', false);

        $this->post(route('admin.students.store'), [
            'dni' => ' 12345678 ',
            'first_name' => ' Ana ',
            'last_name' => ' García ',
            'grade' => ' 1° ',
        ])->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $student = Student::where('dni', '12345678')->firstOrFail();
        $this->assertSame('ANA', $student->first_name);
        $this->assertSame('GARCÍA', $student->last_name);
        $this->assertSame('1°', $student->grade);
        $this->actingAs($admin)
            ->get(route('admin.students.edit', $student->id))
            ->assertOk()
            ->assertSee('Editar alumno')
            ->assertSee('value="12345678"', false)
            ->assertSee('value="ANA"', false);

        $this->put(route('admin.students.update', $student->id), [
            'dni' => '12345678',
            'first_name' => 'Ana María',
            'last_name' => 'García',
            'grade' => '2°',
        ])->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'dni' => '12345678',
            'first_name' => 'ANA MARÍA',
            'grade' => '2°',
        ]);

        $this->delete(route('admin.students.destroy', $student->id))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_student_directory_displays_a_useful_empty_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Aún no hay alumnos registrados')
            ->assertSee('Registrar alumno');
    }

    public function test_student_forms_reject_duplicate_dni_and_keep_existing_dni_on_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentAccount = User::factory()->create(['role' => 'alumno']);
        $teacherAccount = User::factory()->create(['role' => 'docente']);
        $existing = Student::create([
            'dni' => '12345678',
            'first_name' => 'Ana',
            'last_name' => 'García',
            'user_id' => $studentAccount->id,
        ]);
        $other = Student::create([
            'dni' => '87654321',
            'first_name' => 'Luis',
            'last_name' => 'Pérez',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.students.store'), [
                'dni' => '12345678',
                'first_name' => 'Otro',
                'last_name' => 'Alumno',
            ])
            ->assertSessionHasErrors('dni');

        $this->put(route('admin.students.update', $other->id), [
            'dni' => $existing->dni,
            'first_name' => 'Luis',
            'last_name' => 'Pérez',
        ])->assertSessionHasErrors('dni');

        $this->post(route('admin.students.store'), [
            'dni' => '22223333',
            'first_name' => 'Docente',
            'last_name' => 'Cuenta',
            'user_id' => $teacherAccount->id,
        ])->assertSessionHasErrors('user_id');

        $this->post(route('admin.students.store'), [
            'dni' => '33334444',
            'first_name' => 'Duplicado',
            'last_name' => 'Cuenta',
            'user_id' => $studentAccount->id,
        ])->assertSessionHasErrors('user_id');

        $this->post(route('admin.students.store'), [
            'dni' => '44445555',
            'first_name' => 'Grado',
            'last_name' => 'Invalido',
            'grade' => '1° Secundaria',
        ])->assertSessionHasErrors('grade');

        $this->put(route('admin.students.update', $existing->id), [
            'dni' => $existing->dni,
            'first_name' => 'Ana',
            'last_name' => 'García',
        ])->assertRedirect(route('admin.students.index'))
            ->assertSessionHasNoErrors();
    }

    public function test_student_directory_and_mutations_are_restricted_to_admins(): void
    {
        $studentUser = User::factory()->create(['role' => 'alumno']);
        $student = Student::create([
            'dni' => '12345678',
            'first_name' => 'Ana',
            'last_name' => 'García',
        ]);

        $this->actingAs($studentUser)
            ->get(route('admin.students.index'))
            ->assertForbidden();

        $this->get(route('admin.students.create'))
            ->assertForbidden();

        $this->post(route('admin.students.store'), [
            'dni' => '87654321',
            'first_name' => 'Luis',
            'last_name' => 'Pérez',
        ])->assertForbidden();

        $this->get(route('admin.students.edit', $student->id))
            ->assertForbidden();

        $this->put(route('admin.students.update', $student->id), [
            'dni' => '12345678',
            'first_name' => 'Ana',
            'last_name' => 'García',
        ])->assertForbidden();

        $this->delete(route('admin.students.destroy', $student->id))
            ->assertForbidden();
        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }
}
