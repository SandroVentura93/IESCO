<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\PedagogicalResource;
use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectorRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_director_can_manage_academic_modules_but_not_system_administration(): void
    {
        $director = User::factory()->create(['role' => 'director']);
        $this->actingAs($director);

        $this->get(route('director.dashboard'))
            ->assertOk()
            ->assertSee('Panel de dirección')
            ->assertSee('Recursos pedagógicos')
            ->assertDontSee('Gestión de usuarios')
            ->assertDontSee('Configuración del sitio');

        $this->get(route('admin.students.index'))->assertOk();
        $this->get(route('admin.courses.index'))->assertOk();
        $this->get(route('admin.assignments.create'))->assertOk();
        $this->get(route('admin.enrollments.index'))->assertOk();
        $this->get(route('director.resources.index'))->assertOk();

        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.files.panel'))->assertForbidden();
        $this->get(route('admin.settings.index'))->assertForbidden();
    }

    public function test_director_can_upload_and_remove_pedagogical_resources(): void
    {
        Storage::fake('public');
        $director = User::factory()->create(['role' => 'director']);
        $course = Course::create(['name' => 'Ciencias']);

        $this->actingAs($director)
            ->post(route('director.resources.store'), [
                'section' => 'Recursos_Pedagogicos',
                'subfolder' => '2° Secundaria',
                'course_id' => $course->id,
                'week' => 'Unidad 1',
                'file_type' => 'auto',
                'visibility' => 'published',
                'description' => 'Material para reforzar la unidad.',
                'file' => UploadedFile::fake()->create('guia.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('director.resources.index'))
            ->assertSessionHas('success');

        $resource = PedagogicalResource::firstOrFail();
        $this->assertSame($director->id, $resource->user_id);
        $this->assertSame($course->id, $resource->course_id);
        Storage::disk('public')->assertExists($resource->path);
        $storedFile = StoredFile::firstOrFail();
        $this->assertSame('Recursos_Pedagogicos', $storedFile->section);
        $this->assertSame($course->id, $storedFile->course_id);
        $this->assertSame('public', $storedFile->disk);
        $this->assertSame($resource->path, $storedFile->path);
        $this->assertSame('Material para reforzar la unidad.', $storedFile->description);
        $this->assertStringStartsWith(
            'files/recursos-pedagogicos/2-secundaria/curso-'.$course->id.'/documento/',
            $storedFile->path
        );

        $this->delete(route('director.resources.destroy', $resource->id))
            ->assertRedirect(route('director.resources.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('pedagogical_resources', ['id' => $resource->id]);
        $this->assertDatabaseMissing('stored_files', ['id' => $storedFile->id]);
        Storage::disk('public')->assertMissing($resource->path);
    }

    public function test_director_can_upload_to_other_admin_resource_folders_and_see_the_saved_location(): void
    {
        Storage::fake('public');
        $director = User::factory()->create(['role' => 'director']);
        $course = Course::create(['name' => 'Ciencias']);

        $this->actingAs($director)
            ->post(route('director.resources.store'), [
                'section' => 'Actividades_Extracurriculares',
                'subfolder' => '1° Secundaria',
                'course_id' => $course->id,
                'file_type' => 'auto',
                'visibility' => 'published',
                'description' => 'Registro de actividad institucional.',
                'file' => UploadedFile::fake()->create('actividad.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('director.resources.index'));

        $storedFile = StoredFile::firstOrFail();
        $this->assertSame('Actividades_Extracurriculares', $storedFile->section);
        $this->assertStringStartsWith(
            'files/actividades-extracurriculares/1-secundaria/curso-'.$course->id.'/documento/',
            $storedFile->path
        );

        $this->get(route('director.resources.index'))
            ->assertOk()
            ->assertSee('Archivos de otras carpetas')
            ->assertSee('Actividades Extracurriculares')
            ->assertSee('Registro de actividad institucional.')
            ->assertSee($director->name)
            ->assertSee('application/pdf')
            ->assertSee('Detalles del archivo')
            ->assertSee('storage/app/public/'.$storedFile->path);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.files.panel'))
            ->assertOk()
            ->assertSee($storedFile->name);

        $this->delete(route('director.resources.files.destroy', $storedFile->id))
            ->assertRedirect(route('director.resources.index'));

        $this->assertDatabaseMissing('stored_files', ['id' => $storedFile->id]);
        Storage::disk('public')->assertMissing($storedFile->path);
    }

    public function test_director_can_upload_to_the_strategic_plan_folder_without_a_grade_or_course(): void
    {
        Storage::fake('public');
        $director = User::factory()->create(['role' => 'director']);

        $this->actingAs($director)
            ->post(route('director.resources.store'), [
                'section' => 'Plan_Estrategico_IJENT',
                'file_type' => 'auto',
                'visibility' => 'published',
                'file' => UploadedFile::fake()->create('plan.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('director.resources.index'));

        $storedFile = StoredFile::firstOrFail();
        $this->assertSame('Plan_Estrategico_IJENT', $storedFile->section);
        $this->assertNull($storedFile->course_id);
        $this->assertStringStartsWith('files/plan-estrategico-ijent/', $storedFile->path);
        Storage::disk('public')->assertExists($storedFile->path);
    }

    public function test_private_files_stay_on_the_protected_disk_and_are_downloadable_only_by_managers(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $director = User::factory()->create(['role' => 'director']);

        $this->actingAs($director)
            ->post(route('director.resources.store'), [
                'section' => 'Plan_Estrategico_IJENT',
                'file_type' => 'auto',
                'visibility' => 'private',
                'file' => UploadedFile::fake()->create('borrador.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('director.resources.index'));

        $storedFile = StoredFile::firstOrFail();
        $this->assertSame('private', $storedFile->visibility);
        $this->assertSame('local', $storedFile->disk);
        Storage::disk('local')->assertExists($storedFile->path);
        Storage::disk('public')->assertMissing($storedFile->path);

        $this->get(route('public.files.show', $storedFile->id))->assertNotFound();
        $this->get(route('director.resources.files.download', $storedFile->id))->assertOk();
        $this->get(route('director.resources.index'))
            ->assertOk()
            ->assertSee('storage/app/private/'.$storedFile->path)
            ->assertSee('Privado');
    }

    public function test_director_cannot_upload_to_unavailable_folders_or_invalid_subfolders(): void
    {
        $director = User::factory()->create(['role' => 'director']);
        $course = Course::create(['name' => 'Ciencias']);
        $this->actingAs($director);

        $this->post(route('director.resources.store'), [
            'section' => 'Recursos_Pedagogicos',
            'subfolder' => 'Grado inválido',
            'course_id' => $course->id,
            'week' => 'Unidad 1',
            'file_type' => 'auto',
            'visibility' => 'published',
            'file' => UploadedFile::fake()->create('guia.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('subfolder');

        $this->post(route('director.resources.store'), [
            'section' => 'Novedades',
            'file_type' => 'auto',
            'visibility' => 'published',
            'file' => UploadedFile::fake()->create('guia.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('section');

        $this->assertDatabaseCount('stored_files', 0);
        $this->assertDatabaseCount('pedagogical_resources', 0);
    }

    public function test_admin_can_assign_director_role_from_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('value="director"', false)
            ->assertSee('Director');

        $this->post(route('admin.users.store'), [
            'name' => 'Directora Académica',
            'email' => 'directora@example.test',
            'password' => 'password123',
            'role' => 'director',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'directora@example.test',
            'role' => 'director',
        ]);
    }
}
