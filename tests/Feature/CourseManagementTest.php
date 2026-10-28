<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\PedagogicalResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_course_directory_shows_search_results_assignment_counts_and_resources(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['name' => 'Ana Docente', 'role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias Naturales']);
        Course::create(['name' => 'Historia']);
        CourseAssignment::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'section' => 'A',
        ]);
        PedagogicalResource::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'week' => 'Semana 1',
            'name' => 'Guia.pdf',
            'path' => 'pedagogical/guia.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.courses.index', ['q' => 'Ciencias']))
            ->assertOk()
            ->assertSee('Catálogo académico')
            ->assertSee('Gestión de cursos')
            ->assertSee('Total de cursos')
            ->assertSee('Asignaciones docentes')
            ->assertSee('Ciencias Naturales')
            ->assertSee('Ana Docente')
            ->assertSee('En uso')
            ->assertDontSee('Historia')
            ->assertSee('name="q" value="Ciencias"', false);
    }

    public function test_admin_can_create_update_and_delete_courses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), ['name' => '  Artes  '])
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success');

        $course = Course::where('name', 'Artes')->firstOrFail();

        $this->put(route('admin.courses.update', $course->id), ['name' => 'Artes visuales'])
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'Artes visuales']);

        $this->delete(route('admin.courses.destroy', $course->id))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_courses_with_teacher_assignments_or_resources_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);
        CourseAssignment::create(['user_id' => $teacher->id, 'course_id' => $course->id]);

        $this->actingAs($admin)
            ->delete(route('admin.courses.destroy', $course->id))
            ->assertSessionHasErrors('course');
        $this->assertDatabaseHas('courses', ['id' => $course->id]);

        CourseAssignment::query()->delete();
        PedagogicalResource::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'week' => 'Semana 1',
            'name' => 'Guia.pdf',
            'path' => 'pedagogical/guia.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
        ]);

        $this->delete(route('admin.courses.destroy', $course->id))
            ->assertSessionHasErrors('course');
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_course_assignments_are_visible_on_the_teacher_dashboard_and_deduplicated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);

        $this->actingAs($admin)
            ->post(route('admin.assignments.store'), [
                'teacher_id' => $teacher->id,
                'course_id' => $course->id,
                'grade' => '1°',
                'section' => 'A',
            ])
            ->assertRedirect(route('admin.assignments.create'));

        $this->assertDatabaseHas('course_teacher_assignments', [
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '1°',
            'section' => 'A',
        ]);
        $this->assertTrue($teacher->fresh()->courses->contains('id', $course->id));

        $this->post(route('teacher.assign.course'), [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '2°',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('course_teacher_assignments', 1);
        $this->assertDatabaseHas('course_teacher_assignments', [
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '1°',
        ]);
        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Ciencias')
            ->assertSee('Grado: 1°')
            ->assertSee('Sección A');
    }

    public function test_existing_teacher_course_links_are_migrated_to_the_canonical_assignment_table(): void
    {
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);
        DB::table('course_teacher')->insert([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_10_24_000000_consolidate_course_teacher_assignments.php');
        $migration->up();

        $this->assertDatabaseHas('course_teacher_assignments', [
            'user_id' => $teacher->id,
            'course_id' => $course->id,
        ]);
        $this->assertTrue($teacher->fresh()->courses->contains('id', $course->id));
    }

    public function test_assignment_form_rejects_non_teachers_and_prevents_duplicate_assignments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'alumno']);
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);

        $this->actingAs($admin)
            ->post(route('admin.assignments.store'), [
                'teacher_id' => $student->id,
                'course_id' => $course->id,
            ])
            ->assertSessionHasErrors('teacher_id');

        $this->post(route('admin.assignments.store'), [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '1°',
        ])->assertRedirect(route('admin.assignments.create'));

        $this->post(route('admin.assignments.store'), [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '1°',
        ])->assertSessionHasErrors('course_id');

        $this->assertDatabaseCount('course_teacher_assignments', 1);
    }

    public function test_admin_assignment_requires_and_saves_the_teacher_grade(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);

        $this->actingAs($admin)
            ->post(route('admin.assignments.store'), [
                'teacher_id' => $teacher->id,
                'course_id' => $course->id,
                'section' => 'B',
            ])
            ->assertSessionHasErrors('grade');

        $this->assertDatabaseCount('course_teacher_assignments', 0);

        $this->post(route('admin.assignments.store'), [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '3° Secundaria',
            'section' => 'B',
        ])->assertSessionHasErrors('grade');

        CourseAssignment::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'section' => 'B',
        ]);

        $this->post(route('admin.assignments.store'), [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '3°',
            'section' => 'B',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('course_teacher_assignments', [
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '3°',
            'section' => 'B',
        ]);
    }

    public function test_assignment_form_guides_admin_through_teacher_course_and_section_selection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'docente', 'name' => 'Ana Docente']);
        $student = User::factory()->create(['role' => 'alumno', 'name' => 'Alumno Excluido']);
        $course = Course::create(['name' => 'Ciencias Naturales']);

        $this->actingAs($admin)
            ->get(route('admin.assignments.create'))
            ->assertOk()
            ->assertSee('Asignar curso a docente')
            ->assertSee('Busca o selecciona un docente')
            ->assertSee('role="listbox"', false)
            ->assertSee('class="assignment-picker__option"', false)
            ->assertDontSee('<datalist', false)
            ->assertSee('Ana Docente')
            ->assertSee($teacher->email)
            ->assertSee('Ciencias Naturales')
            ->assertSee('Grado')
            ->assertSee('6°')
            ->assertSee('Otro')
            ->assertSee('Sección')
            ->assertSee('Vista previa')
            ->assertDontSee('Alumno Excluido')
            ->assertSee('summary-grade', false);
    }

    public function test_assignment_form_explains_when_teachers_or_courses_are_unavailable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.assignments.create'))
            ->assertOk()
            ->assertSee('Aún no hay docentes ni cursos para asignar.')
            ->assertSee(route('admin.users.create'))
            ->assertSee(route('admin.courses.create'))
            ->assertSee('type="submit" disabled', false);
    }

    public function test_assignment_directory_lists_searches_edits_and_deletes_assignments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'docente', 'name' => 'Ana Docente']);
        $otherTeacher = User::factory()->create(['role' => 'docente', 'name' => 'Luis Profesor']);
        $course = Course::create(['name' => 'Ciencias Naturales']);
        $otherCourse = Course::create(['name' => 'Comunicación']);
        $assignment = CourseAssignment::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '3°',
            'section' => 'A',
        ]);
        $otherAssignment = CourseAssignment::create([
            'user_id' => $otherTeacher->id,
            'course_id' => $otherCourse->id,
            'grade' => '4°',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.assignments.create'))
            ->assertOk()
            ->assertSee('Asignaciones registradas')
            ->assertSee('2 registros')
            ->assertSee('Ana Docente')
            ->assertSee('Ciencias Naturales')
            ->assertSee('3°')
            ->assertSee('Sección')
            ->assertSee(route('admin.assignments.edit', $assignment->id));

        $this->get(route('admin.assignments.create', ['search' => 'Ana Docente']))
            ->assertOk()
            ->assertSee('data-assignment-id="'.$assignment->id.'"', false)
            ->assertDontSee('data-assignment-id="'.$otherAssignment->id.'"', false);

        $this->get(route('admin.assignments.edit', $assignment->id))
            ->assertOk()
            ->assertSee('Editar asignación')
            ->assertSee('value="3°" selected', false)
            ->assertSee('value="A"', false)
            ->assertSee(route('admin.assignments.update', $assignment->id));

        $this->put(route('admin.assignments.update', $assignment->id), [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '5°',
            'section' => 'B',
        ])->assertRedirect(route('admin.assignments.create'));

        $this->assertDatabaseHas('course_teacher_assignments', [
            'id' => $assignment->id,
            'grade' => '5°',
            'section' => 'B',
        ]);

        $this->delete(route('admin.assignments.destroy', $assignment->id))
            ->assertRedirect(route('admin.assignments.create'));

        $this->assertDatabaseMissing('course_teacher_assignments', ['id' => $assignment->id]);
    }

    public function test_assignment_update_prevents_duplicate_teacher_course_pairs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);
        $existing = CourseAssignment::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '1°',
        ]);
        $legacy = CourseAssignment::create([
            'user_id' => User::factory()->create(['role' => 'docente'])->id,
            'course_id' => Course::create(['name' => 'Historia'])->id,
            'grade' => '2°',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.assignments.update', $legacy->id), [
                'teacher_id' => $teacher->id,
                'course_id' => $course->id,
                'grade' => '3°',
            ])
            ->assertSessionHasErrors('course_id');

        $this->assertDatabaseHas('course_teacher_assignments', [
            'id' => $existing->id,
            'grade' => '1°',
        ]);
        $this->assertDatabaseHas('course_teacher_assignments', [
            'id' => $legacy->id,
            'grade' => '2°',
        ]);
    }

    public function test_non_admins_cannot_open_or_manage_courses(): void
    {
        $teacher = User::factory()->create(['role' => 'docente']);

        $this->actingAs($teacher)
            ->get(route('admin.courses.index'))
            ->assertForbidden();
        $this->post(route('admin.courses.store'), ['name' => 'Privado'])
            ->assertForbidden();
        $this->get(route('admin.assignments.create'))->assertForbidden();
        $this->put(route('admin.assignments.update', 1), [])->assertForbidden();
        $this->delete(route('admin.assignments.destroy', 1))->assertForbidden();
    }
}
