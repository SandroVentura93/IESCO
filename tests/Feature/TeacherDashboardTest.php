<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\PedagogicalResource;
use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_dashboard_shows_only_the_activity_summary_and_navigation(): void
    {
        $teacher = User::factory()->create(['role' => 'docente']);
        $otherTeacher = User::factory()->create(['role' => 'docente']);
        $assignedCourse = Course::create(['name' => 'Ciencias']);
        $unassignedCourse = Course::create(['name' => 'Historia']);
        CourseAssignment::create([
            'user_id' => $teacher->id,
            'course_id' => $assignedCourse->id,
            'grade' => '7.°',
            'section' => 'A',
        ]);

        PedagogicalResource::create([
            'user_id' => $teacher->id,
            'course_id' => $assignedCourse->id,
            'week' => 'Semana 1',
            'name' => 'Guia de clase.pdf',
            'path' => 'pedagogical/guia-de-clase.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
        ]);
        PedagogicalResource::create([
            'user_id' => $otherTeacher->id,
            'course_id' => $assignedCourse->id,
            'week' => 'Semana 2',
            'name' => 'Material de otro docente.pdf',
            'path' => 'pedagogical/otro-docente.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Hola,')
            ->assertSee('Mis cursos')
            ->assertSee((string) $teacher->courses()->count())
            ->assertDontSee('Ciencias')
            ->assertDontSee('Historia')
            ->assertDontSee('Guia de clase.pdf')
            ->assertDontSee('Material de otro docente.pdf')
            ->assertSee('Subir material')
            ->assertSee('Menú docente')
            ->assertSee('teacher-dashboard__workspace', false)
            ->assertSee('teacher-dashboard__sidebar', false)
            ->assertSee('Espacio de enseñanza')
            ->assertSee('Mis recursos')
            ->assertSee('Subir recursos pedagógicos')
            ->assertSee('Funciones de tu cuenta docente')
            ->assertSee('elimina solo tus propios recursos')
            ->assertSee(route('teacher.courses'), false)
            ->assertSee(route('teacher.resources.index'), false)
            ->assertSee(route('teacher.resources.create'), false);
    }

    public function test_each_teacher_menu_item_opens_a_separate_page(): void
    {
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);
        CourseAssignment::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'grade' => '7.°',
            'section' => 'A',
        ]);
        PedagogicalResource::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'week' => 'Semana 1',
            'name' => 'Guia propia.pdf',
            'path' => 'pedagogical/guia-propia.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses'))
            ->assertOk()
            ->assertSee('Ciencias')
            ->assertSee('Grado: 7.°')
            ->assertDontSee('Guia propia.pdf')
            ->assertDontSee('Grado / subcarpeta')
            ->assertSee(route('teacher.courses'), false);

        $this->get(route('teacher.resources.index'))
            ->assertOk()
            ->assertSee('Guia propia.pdf')
            ->assertDontSee('Grado / subcarpeta')
            ->assertSee(route('teacher.resources.index'), false);

        $this->get(route('teacher.resources.create'))
            ->assertOk()
            ->assertSee('Grado / subcarpeta')
            ->assertSee('Tipo de archivo')
            ->assertSee('name="description"', false)
            ->assertSee('name="available_from"', false)
            ->assertSee('name="available_until"', false)
            ->assertSee('Ciencias')
            ->assertDontSee('Guia propia.pdf')
            ->assertSee(route('teacher.resources.create'), false);
    }

    public function test_only_teachers_can_open_the_teacher_dashboard(): void
    {
        foreach (['teacher.dashboard', 'teacher.courses', 'teacher.resources.index', 'teacher.resources.create'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        foreach (['teacher.dashboard', 'teacher.courses', 'teacher.resources.index', 'teacher.resources.create'] as $route) {
            $this->get(route($route))->assertForbidden();
        }

        $teacher = User::factory()->create(['role' => 'docente']);
        $this->actingAs($teacher)
            ->get(route('admin.users.index'))
            ->assertForbidden();
        $this->get(route('admin.files.panel'))->assertForbidden();
        $this->get(route('admin.assignments.create'))->assertForbidden();
        $this->post(route('teacher.assign.course'), [
            'teacher_id' => $teacher->id,
            'course_id' => 1,
            'grade' => '1°',
        ])->assertForbidden();
    }

    public function test_teacher_can_upload_to_an_assigned_course_but_not_to_another_course(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->create(['role' => 'docente']);
        $assignedCourse = Course::create(['name' => 'Ciencias']);
        $unassignedCourse = Course::create(['name' => 'Historia']);
        CourseAssignment::create(['user_id' => $teacher->id, 'course_id' => $assignedCourse->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.resources.upload'), [
                'section' => 'Recursos_Pedagogicos',
                'course_id' => $unassignedCourse->id,
                'subfolder' => '1° Secundaria',
                'file_type' => 'auto',
                'week' => 'Semana 1',
                'file' => UploadedFile::fake()->create('material.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('course_id');

        $this->assertDatabaseCount('pedagogical_resources', 0);
        Storage::disk('public')->assertDirectoryEmpty('pedagogical');

        $this->post(route('teacher.resources.upload'), [
            'section' => 'Recursos_Pedagogicos',
            'course_id' => $assignedCourse->id,
            'subfolder' => '2° Secundaria',
            'file_type' => 'auto',
            'description' => 'Guía para trabajar los conceptos de la unidad.',
            'available_from' => today()->toDateString(),
            'available_until' => today()->addDays(7)->toDateString(),
            'week' => 'Semana 1',
            'file' => UploadedFile::fake()->create('material.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('teacher.resources.index'));

        $resource = PedagogicalResource::firstOrFail();
        $storedFile = StoredFile::firstOrFail();
        $this->assertSame($assignedCourse->id, $resource->course_id);
        $this->assertSame($teacher->id, $resource->user_id);
        $this->assertSame('Recursos_Pedagogicos', $storedFile->section);
        $this->assertSame('published', $storedFile->visibility);
        $this->assertSame($assignedCourse->id, $storedFile->course_id);
        $this->assertSame('Guía para trabajar los conceptos de la unidad.', $storedFile->description);
        $this->assertSame(today()->toDateString(), $storedFile->available_from->toDateString());
        $this->assertSame(today()->addDays(7)->toDateString(), $storedFile->available_until->toDateString());
        $this->assertStringStartsWith(
            'files/recursos-pedagogicos/2-secundaria/curso-'.$assignedCourse->id.'/documento/',
            $resource->path
        );
        $this->assertSame($resource->path, $storedFile->path);
        Storage::disk('public')->assertExists($resource->path);

        $this->get(route('teacher.resources.index'))
            ->assertOk()
            ->assertSee('storage/app/public/'.$resource->path)
            ->assertSee(route('teacher.resources.destroy', $resource->id), false)
            ->assertSee('Recursos Pedagógicos')
            ->assertSee('Guía para trabajar los conceptos de la unidad.')
            ->assertSee('application/pdf')
            ->assertSee('Detalles del archivo')
            ->assertSee(today()->format('d/m/Y'))
            ->assertSee(today()->addDays(7)->format('d/m/Y'))
            ->assertSee($teacher->name)
            ->assertDontSee('Grado / subcarpeta');
    }

    public function test_teacher_cannot_upload_a_resource_with_an_end_date_before_its_start_date(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);
        CourseAssignment::create(['user_id' => $teacher->id, 'course_id' => $course->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.resources.upload'), [
                'section' => 'Recursos_Pedagogicos',
                'course_id' => $course->id,
                'subfolder' => '1° Secundaria',
                'file_type' => 'auto',
                'description' => 'Material de prueba.',
                'available_from' => '2026-10-26',
                'available_until' => '2026-10-25',
                'week' => 'Semana 1',
                'file' => UploadedFile::fake()->create('material.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('available_until');

        $this->assertDatabaseCount('stored_files', 0);
        $this->assertDatabaseCount('pedagogical_resources', 0);
        Storage::disk('public')->assertDirectoryEmpty('files');
    }

    public function test_teacher_can_upload_extracurricular_files_only_to_an_assigned_course(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->create(['role' => 'docente']);
        $otherTeacher = User::factory()->create(['role' => 'docente']);
        $assignedCourse = Course::create(['name' => 'Ciencias']);
        $unassignedCourse = Course::create(['name' => 'Historia']);
        CourseAssignment::create(['user_id' => $teacher->id, 'course_id' => $assignedCourse->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.resources.upload'), [
                'section' => 'Actividades_Extracurriculares',
                'course_id' => $unassignedCourse->id,
                'subfolder' => '1° Secundaria',
                'file_type' => 'auto',
                'file' => UploadedFile::fake()->create('actividad.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('course_id');

        $this->post(route('teacher.resources.upload'), [
            'section' => 'Actividades_Extracurriculares',
            'course_id' => $assignedCourse->id,
            'subfolder' => '1° Secundaria',
            'file_type' => 'auto',
            'file' => UploadedFile::fake()->create('actividad.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('teacher.resources.index'));

        $storedFile = StoredFile::firstOrFail();
        $this->assertSame('Actividades_Extracurriculares', $storedFile->section);
        $this->assertSame($assignedCourse->id, $storedFile->course_id);
        $this->assertStringStartsWith(
            'files/actividades-extracurriculares/1-secundaria/curso-'.$assignedCourse->id.'/documento/',
            $storedFile->path
        );
        $this->assertDatabaseCount('pedagogical_resources', 0);
        Storage::disk('public')->assertExists($storedFile->path);

        $otherFile = StoredFile::create([
            'user_id' => $otherTeacher->id,
            'name' => 'Archivo ajeno.pdf',
            'path' => 'files/actividades-extracurriculares/1-secundaria/curso-'.$assignedCourse->id.'/documento/otro.pdf',
            'disk' => 'public',
            'mime' => 'application/pdf',
            'size' => 12,
            'visibility' => 'published',
            'published_at' => now(),
            'course_id' => $assignedCourse->id,
            'file_type' => 'document',
            'section' => 'Actividades_Extracurriculares',
        ]);

        $this->get(route('teacher.resources.index'))
            ->assertOk()
            ->assertSee('actividad.pdf')
            ->assertSee('Actividades Extracurriculares')
            ->assertSee(route('teacher.resources.files.destroy', $storedFile->id), false);

        $this->delete(route('teacher.resources.files.destroy', $otherFile->id))->assertNotFound();
        $this->delete(route('teacher.resources.files.destroy', $storedFile->id))
            ->assertRedirect(route('teacher.resources.index'));
        $this->assertDatabaseMissing('stored_files', ['id' => $storedFile->id]);
        Storage::disk('public')->assertMissing($storedFile->path);
    }

    public function test_teacher_can_upload_institutional_files_without_course_assignment(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->create(['role' => 'docente']);

        $this->actingAs($teacher)
            ->get(route('teacher.resources.create'))
            ->assertOk()
            ->assertSee('Actividades Extracurriculares')
            ->assertSee('Plan Estratégico IJENT')
            ->assertSee('Recursos Pedagógicos')
            ->assertSee('data-course-hierarchy="false"', false);

        $this->post(route('teacher.resources.upload'), [
            'section' => 'Plan_Estrategico_IJENT',
            'file_type' => 'auto',
            'file' => UploadedFile::fake()->create('plan.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('teacher.resources.index'));

        $storedFile = StoredFile::firstOrFail();
        $this->assertSame('Plan_Estrategico_IJENT', $storedFile->section);
        $this->assertNull($storedFile->course_id);
        $this->assertStringStartsWith('files/plan-estrategico-ijent/', $storedFile->path);
        $this->assertDatabaseCount('pedagogical_resources', 0);
        Storage::disk('public')->assertExists($storedFile->path);

        $this->post(route('teacher.resources.upload'), [
            'section' => 'Plan_Estrategico_IJENT',
            'course_id' => 1,
            'subfolder' => '1° Secundaria',
            'file_type' => 'auto',
            'file' => UploadedFile::fake()->create('plan-invalido.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['course_id', 'subfolder']);

        $this->assertDatabaseCount('stored_files', 1);
    }

    public function test_teacher_can_delete_only_their_own_pedagogical_resources(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->create(['role' => 'docente']);
        $otherTeacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);
        CourseAssignment::create(['user_id' => $teacher->id, 'course_id' => $course->id]);
        CourseAssignment::create(['user_id' => $otherTeacher->id, 'course_id' => $course->id]);

        $ownedPath = 'files/recursos-pedagogicos/1-secundaria/curso-'.$course->id.'/documento/material.pdf';
        Storage::disk('public')->put($ownedPath, 'material del docente');
        $ownedResource = PedagogicalResource::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'week' => 'Semana 1',
            'name' => 'Material propio.pdf',
            'path' => $ownedPath,
            'mime' => 'application/pdf',
            'size' => 22,
        ]);
        $ownedStoredFile = StoredFile::create([
            'user_id' => $teacher->id,
            'name' => $ownedResource->name,
            'path' => $ownedPath,
            'disk' => 'public',
            'mime' => 'application/pdf',
            'size' => 22,
            'visibility' => 'published',
            'published_at' => now(),
            'course_id' => $course->id,
            'file_type' => 'document',
            'section' => 'Recursos_Pedagogicos',
        ]);
        $otherResource = PedagogicalResource::create([
            'user_id' => $otherTeacher->id,
            'course_id' => $course->id,
            'week' => 'Semana 2',
            'name' => 'Material ajeno.pdf',
            'path' => 'pedagogical/otro.pdf',
            'mime' => 'application/pdf',
            'size' => 12,
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.resources.destroy', $otherResource->id))
            ->assertNotFound();

        $this->delete(route('teacher.resources.destroy', $ownedResource->id))
            ->assertRedirect(route('teacher.resources.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('pedagogical_resources', ['id' => $ownedResource->id]);
        $this->assertDatabaseMissing('stored_files', ['id' => $ownedStoredFile->id]);
        $this->assertDatabaseHas('pedagogical_resources', ['id' => $otherResource->id]);
        Storage::disk('public')->assertMissing($ownedPath);
    }

    public function test_teacher_cannot_choose_an_unavailable_subfolder_or_target_folder(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->create(['role' => 'docente']);
        $course = Course::create(['name' => 'Ciencias']);
        CourseAssignment::create(['user_id' => $teacher->id, 'course_id' => $course->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.resources.upload'), [
                'section' => 'Recursos_Pedagogicos',
                'course_id' => $course->id,
                'subfolder' => 'Grado inexistente',
                'file_type' => 'auto',
                'week' => 'Semana 1',
                'file' => UploadedFile::fake()->create('material.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('subfolder');

        $this->post(route('teacher.resources.upload'), [
            'section' => 'Novedades',
            'file_type' => 'auto',
            'file' => UploadedFile::fake()->create('fuera-de-alcance.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('section');

        $this->assertDatabaseCount('pedagogical_resources', 0);
        $this->assertDatabaseCount('stored_files', 0);
        Storage::disk('public')->assertDirectoryEmpty('files');
    }
}
