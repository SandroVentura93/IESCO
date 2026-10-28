<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\PedagogicalResource;
use App\Models\StoredFile;
use App\Models\Student;
use App\Models\User;
use App\Support\StoredFileSections;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalProjectFilesTest extends TestCase
{
    use RefreshDatabase;

    private array $courseIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Matemática', 'Comunicación', 'Ciencia y Tecnología'] as $name) {
            $this->courseIds[$name] = Course::firstOrCreate(['name' => $name])->id;
        }
    }

    public function test_admin_can_upload_a_scheduled_file_to_the_pedagogical_resources_hierarchy(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('guia.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'description' => 'Guía para desarrollar la unidad de aprendizaje.',
            'section' => 'Recursos_Pedagogicos',
            'subfolder' => '1° Secundaria',
            'course_id' => $this->courseIds['Ciencia y Tecnología'],
            'available_from' => today()->toDateString(),
            'available_until' => today()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect();
        $file = StoredFile::query()->firstOrFail();
        PedagogicalResource::create([
            'user_id' => $admin->id,
            'course_id' => $this->courseIds['Ciencia y Tecnología'],
            'week' => 'Unidad 1',
            'name' => $file->name,
            'path' => $file->path,
            'mime' => $file->mime,
            'size' => $file->size,
        ]);
        $this->assertSame('local', $file->disk);
        $this->assertSame('Recursos_Pedagogicos', $file->section);
        $this->assertSame('published', $file->visibility);
        $this->assertSame($this->courseIds['Ciencia y Tecnología'], $file->course_id);
        $this->assertStringStartsWith(
            StoredFileSections::directory(
                'Recursos_Pedagogicos',
                '1° Secundaria',
                'document',
                $this->courseIds['Ciencia y Tecnología']
            ).'/',
            $file->path
        );
        Storage::disk('local')->assertExists($file->path);

        $this->getJson(route('drive.local_project', ['section' => 'Recursos_Pedagogicos']))
            ->assertOk()
            ->assertJsonCount(5, 'files')
            ->assertJsonPath('files.0.name', '1° Secundaria');

        $this->getJson(route('drive.local_project', [
            'section' => 'Recursos_Pedagogicos',
            'folder' => '1° Secundaria',
        ]))
            ->assertOk()
            ->assertJsonCount(3, 'files')
            ->assertJsonPath('files.0.name', 'Ciencia y Tecnología');

        $this->getJson(route('drive.local_project', [
            'section' => 'Recursos_Pedagogicos',
            'folder' => '1° Secundaria',
            'course' => $this->courseIds['Ciencia y Tecnología'],
        ]))
            ->assertOk()
            ->assertJsonCount(5, 'files')
            ->assertJsonPath('files.1.name', 'Documento');

        $this->getJson(route('drive.local_project', [
            'section' => 'Recursos_Pedagogicos',
            'folder' => '1° Secundaria',
            'course' => $this->courseIds['Ciencia y Tecnología'],
            'category' => 'document',
        ]))
            ->assertOk()
            ->assertJsonPath('files.0.name', 'guia.pdf')
            ->assertJsonPath('files.0.mimeType', 'application/pdf')
            ->assertJsonPath('files.0.description', 'Guía para desarrollar la unidad de aprendizaje.')
            ->assertJsonPath('files.0.sectionLabel', 'Proyecto IJENT · Recursos pedagógicos')
            ->assertJsonPath('files.0.grade', '1° Secundaria')
            ->assertJsonPath('files.0.courseName', 'Ciencia y Tecnología')
            ->assertJsonPath('files.0.week', 'Unidad 1')
            ->assertJsonPath('files.0.uploadedBy', $admin->name)
            ->assertJsonPath('files.0.fileType', 'Documento')
            ->assertJsonPath('files.0.size', 10240)
            ->assertJsonPath('files.0.availableFrom', today()->toDateString())
            ->assertJsonPath('files.0.availableUntil', today()->addDays(7)->toDateString())
            ->assertJsonPath('files.0.localUrl', route('public.files.show', $file->id));
    }

    public function test_empty_pedagogical_resources_hierarchy_is_browsable_before_files_are_uploaded(): void
    {
        $this->getJson(route('drive.local_project'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => 'local_Recursos_Pedagogicos',
                'name' => 'Proyecto IJENT · Recursos pedagógicos',
            ]);

        $this->getJson(route('drive.local_project', ['section' => 'Recursos_Pedagogicos']))
            ->assertOk()
            ->assertJsonCount(5, 'files')
            ->assertJsonPath('files.0.name', '1° Secundaria');

        $this->getJson(route('drive.local_project', [
            'section' => 'Recursos_Pedagogicos',
            'folder' => '1° Secundaria',
        ]))
            ->assertOk()
            ->assertJsonCount(3, 'files');

        $this->getJson(route('drive.local_project', [
            'section' => 'Recursos_Pedagogicos',
            'folder' => '1° Secundaria',
            'course' => $this->courseIds['Matemática'],
        ]))
            ->assertOk()
            ->assertJsonCount(5, 'files')
            ->assertJsonPath('files.0.name', 'Imagen');

        $this->getJson(route('drive.local_project', [
            'section' => 'Recursos_Pedagogicos',
            'folder' => '1° Secundaria',
            'course' => $this->courseIds['Matemática'],
            'category' => 'document',
        ]))
            ->assertOk()
            ->assertExactJson(['files' => []]);
    }

    public function test_plan_strategic_files_remain_in_the_section_root_without_a_hierarchy(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('plan-anual.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'section' => 'Plan_Estrategico_IJENT',
        ])->assertRedirect();

        $file = StoredFile::query()->firstOrFail();
        $this->assertNull($file->course_id);
        $this->assertStringStartsWith(
            StoredFileSections::directory('Plan_Estrategico_IJENT').'/',
            $file->path
        );

        $this->getJson(route('drive.local_project', ['section' => 'Plan_Estrategico_IJENT']))
            ->assertOk()
            ->assertJsonCount(1, 'files')
            ->assertJsonPath('files.0.name', 'plan-anual.pdf');
    }

    public function test_file_panel_lists_only_project_submenu_destinations_and_stores_uploads_in_the_selected_folder(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.files.panel'))
            ->assertOk()
            ->assertSee('Plan Estratégico IJENT')
            ->assertSee('Actividades Extracurriculares')
            ->assertSee('Recursos Pedagógicos')
            ->assertDontSee('Recursos institucionales</option>')
            ->assertDontSee('Evaluaciones y seguimiento</option>')
            ->assertDontSee('Formación docente 2026</option>')
            ->assertDontSee('Biblioteca multimedia (privada)')
            ->assertDontSee('Novedades</option>')
            ->assertSee('No hay alumnos registrados; puedes continuar sin asignar uno.')
            ->assertSee('id="upload-subfolder"', false)
            ->assertSee('id="upload-course"', false)
            ->assertSee('Matemática')
            ->assertSee('subfoldersBySection', false);

        foreach (StoredFileSections::submenuOptions() as $section => $destination) {
            $payload = [
                'file' => UploadedFile::fake()->create($section.'.pdf', 10, 'application/pdf'),
                'visibility' => 'published',
                'file_type' => 'auto',
                'section' => $section,
            ];
            if (StoredFileSections::hasCourseHierarchy($section)) {
                $payload['subfolder'] = '2° Secundaria';
                $payload['course_id'] = $this->courseIds['Matemática'];
            }
            $this->post(route('admin.files.store'), $payload)->assertRedirect();

            $file = StoredFile::query()->where('section', $section)->latest('id')->firstOrFail();
            $expectedDirectory = StoredFileSections::hasCourseHierarchy($section)
                ? StoredFileSections::directory(
                    $section,
                    '2° Secundaria',
                    'document',
                    $this->courseIds['Matemática']
                )
                : StoredFileSections::directory($section);
            $this->assertStringStartsWith($expectedDirectory.'/', $file->path);
            Storage::disk('local')->assertExists($file->path);

        }
    }

    public function test_file_panel_search_filters_results_and_exposes_the_editor(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $matching = $this->createStoredFile($admin, [
            'name' => 'guia-deportiva.pdf',
            'description' => 'Guía para actividades deportivas',
            'section' => 'Actividades_Extracurriculares',
            'file_type' => 'document',
        ]);
        $other = $this->createStoredFile($admin, [
            'name' => 'plan-anual.pdf',
            'section' => 'Plan_Estrategico_IJENT',
            'file_type' => 'document',
        ]);

        $this->actingAs($admin)->get(route('admin.files.panel', ['q' => 'deportiva']))
            ->assertOk()
            ->assertSee('guia-deportiva.pdf')
            ->assertDontSee('plan-anual.pdf')
            ->assertSee(route('admin.files.edit', $matching->id), false)
            ->assertSee('id="fileEditModal"', false)
            ->assertSee('data-file-id="'.$matching->id.'"', false)
            ->assertSee('data-delete-url="'.route('admin.files.destroy', $matching->id).'"', false)
            ->assertSee('id="fileEditDeleteForm"', false)
            ->assertSee('Eliminar archivo')
            ->assertSee('name="available_from"', false)
            ->assertSee('name="course_id"', false)
            ->assertSee('CENTRO DE CONTENIDO');

        $this->get(route('admin.files.panel', ['visibility' => 'published']))
            ->assertOk()
            ->assertSee('guia-deportiva.pdf')
            ->assertSee('plan-anual.pdf');
    }

    public function test_dashboard_activity_provides_file_ids_for_editing_managed_files(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $managedFile = $this->createStoredFile($admin, [
            'name' => 'guia-para-editar.pdf',
            'section' => 'Plan_Estrategico_IJENT',
        ]);
        StoredFile::create([
            'user_id' => $admin->id,
            'name' => 'archivo-privado.pdf',
            'path' => 'biblioteca-multimedia/archivo-privado.pdf',
            'disk' => 'local',
            'mime' => 'application/pdf',
            'size' => 4,
            'visibility' => 'private',
            'file_type' => 'document',
            'section' => StoredFile::MEDIA_LIBRARY_SECTION,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.activity.recent_json'))
            ->assertOk()
            ->assertJsonFragment([
                'file_id' => $managedFile->id,
                'delete_url' => route('admin.files.destroy', $managedFile->id),
                'title' => 'Archivo: guia-para-editar.pdf',
            ])
            ->assertJsonMissing([
                'title' => 'Archivo: archivo-privado.pdf',
            ]);

        $this->get(route('admin.files.panel', ['edit' => $managedFile->id]))
            ->assertOk()
            ->assertSee('id="fileEditModal"', false)
            ->assertSee('data-file-id="'.$managedFile->id.'"', false);
    }

    public function test_home_page_includes_an_accessible_video_player_modal(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="videoPreviewModal"', false)
            ->assertSee('id="videoPreviewTitle"', false)
            ->assertSee('id="videoPreviewPlayer" controls playsinline', false)
            ->assertSee('aria-label="Cerrar reproductor"', false)
            ->assertSee('id="imagePreviewModal"', false)
            ->assertSee('id="imagePreviewPlayer"', false)
            ->assertSee('id="imagePreviewDescription"', false)
            ->assertSee('aria-label="Cerrar imagen"', false)
            ->assertSee('id="pdfPreviewModal"', false)
            ->assertSee('id="pdfPreviewFrame"', false)
            ->assertSee('id="pdfPreviewDescription"', false)
            ->assertSee('aria-label="Cerrar documento PDF"', false);
    }

    public function test_published_pdf_is_listed_and_served_inline_for_modal_preview(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $pdf = $this->createStoredFile($owner, [
            'name' => 'guia-escolar.pdf',
            'description' => 'Guía informativa para las familias.',
            'path' => StoredFileSections::directory('Proyecto_IJENT').'/guia-escolar.pdf',
            'mime' => 'application/pdf',
            'file_type' => 'document',
            'section' => 'Proyecto_IJENT',
        ]);

        $this->getJson(route('drive.local_project', ['section' => 'Proyecto_IJENT']))
            ->assertOk()
            ->assertJsonPath('files.0.name', 'guia-escolar.pdf')
            ->assertJsonPath('files.0.description', 'Guía informativa para las familias.')
            ->assertJsonPath('files.0.mimeType', 'application/pdf')
            ->assertJsonPath('files.0.localUrl', route('public.files.show', $pdf->id));

        $response = $this->get(route('public.files.show', $pdf->id));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringNotContainsString(
            'attachment',
            $response->headers->get('Content-Disposition', '')
        );
    }

    public function test_admin_can_upload_to_an_activities_grade_subfolder_and_public_feed_lists_it_under_that_folder(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('deportes.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'section' => 'Actividades_Extracurriculares',
            'subfolder' => '2° Secundaria',
            'course_id' => $this->courseIds['Matemática'],
        ])->assertRedirect();

        $file = StoredFile::query()->firstOrFail();
        $this->assertStringStartsWith(
            StoredFileSections::directory(
                'Actividades_Extracurriculares',
                '2° Secundaria',
                'document',
                $this->courseIds['Matemática']
            ).'/',
            $file->path
        );
        $this->assertSame($this->courseIds['Matemática'], $file->course_id);
        Storage::disk('local')->assertExists($file->path);

        $this->getJson(route('drive.local_project', [
            'section' => 'Actividades_Extracurriculares',
        ]))
            ->assertOk()
            ->assertJsonCount(5, 'files')
            ->assertJsonPath('files.1.name', '2° Secundaria');

        foreach (array_keys(StoredFileSections::subfolders('Actividades_Extracurriculares')) as $grade) {
            $gradeResponse = $this->getJson(route('drive.local_project', [
                'section' => 'Actividades_Extracurriculares',
                'folder' => $grade,
            ]))
                ->assertOk()
                ->assertJsonCount(3, 'files')
                ->assertJsonPath('files.0.name', 'Ciencia y Tecnología')
                ->assertJsonPath('files.1.name', 'Comunicación')
                ->assertJsonPath('files.2.name', 'Matemática');

            foreach ($gradeResponse->json('files') as $courseFolder) {
                $this->getJson(route('drive.local_project', [
                    'section' => 'Actividades_Extracurriculares',
                    'folder' => $grade,
                    'course' => $this->courseIds[$courseFolder['name']],
                ]))
                    ->assertOk()
                    ->assertJsonCount(5, 'files')
                    ->assertJsonPath('files.0.name', 'Imagen')
                    ->assertJsonPath('files.1.name', 'Documento')
                    ->assertJsonPath('files.2.name', 'Video')
                    ->assertJsonPath('files.3.name', 'Audio')
                    ->assertJsonPath('files.4.name', 'Otro');
            }
        }

        $this->getJson(route('drive.local_project', [
            'section' => 'Actividades_Extracurriculares',
            'folder' => '2° Secundaria',
            'course' => $this->courseIds['Matemática'],
        ]))
            ->assertOk()
            ->assertJsonCount(5, 'files')
            ->assertJsonPath('files.0.name', 'Imagen')
            ->assertJsonPath('files.1.name', 'Documento')
            ->assertJsonPath('files.2.name', 'Video')
            ->assertJsonPath('files.3.name', 'Audio')
            ->assertJsonPath('files.4.name', 'Otro');

        $this->getJson(route('drive.local_project', [
            'section' => 'Actividades_Extracurriculares',
            'folder' => '2° Secundaria',
            'course' => $this->courseIds['Matemática'],
            'category' => 'document',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'files')
            ->assertJsonPath('files.0.name', 'deportes.pdf');
    }

    public function test_admin_must_choose_a_course_when_uploading_to_an_activities_grade(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('deportes.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'section' => 'Actividades_Extracurriculares',
            'subfolder' => '2° Secundaria',
        ])->assertSessionHasErrors('course_id');

        $this->assertDatabaseCount('stored_files', 0);
    }

    public function test_admin_can_optionally_associate_an_activities_file_with_a_student(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'dni' => 'STUDENT-1001',
            'first_name' => 'Ana',
            'last_name' => 'Pérez',
            'grade' => '2° Secundaria',
        ]);

        $this->actingAs($admin)->get(route('admin.files.panel'))
            ->assertOk()
            ->assertSee('Alumno (opcional)')
            ->assertSee('Pérez, Ana · 2° Secundaria');

        $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('deportes.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'section' => 'Actividades_Extracurriculares',
            'subfolder' => '2° Secundaria',
            'course_id' => $this->courseIds['Matemática'],
            'student_id' => $student->id,
        ])->assertRedirect();

        $file = StoredFile::query()->with('student')->firstOrFail();
        $this->assertSame($student->id, $file->student_id);
        $this->assertSame('Pérez', $file->student->last_name);
        $this->assertStringStartsWith(
            StoredFileSections::directory(
                'Actividades_Extracurriculares',
                '2° Secundaria',
                'document',
                $this->courseIds['Matemática']
            ).'/',
            $file->path
        );
        Storage::disk('local')->assertExists($file->path);
    }

    public function test_student_is_optional_and_cannot_be_assigned_to_another_section(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'dni' => 'STUDENT-1002',
            'first_name' => 'Luis',
            'last_name' => 'García',
        ]);

        $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('actividad.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'section' => 'Actividades_Extracurriculares',
            'subfolder' => '1° Secundaria',
            'course_id' => $this->courseIds['Matemática'],
        ])->assertRedirect();

        $this->assertNull(StoredFile::query()->firstOrFail()->student_id);

        $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('plan.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'section' => 'Plan_Estrategico_IJENT',
            'student_id' => $student->id,
        ])->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('stored_files', 1);
    }

    public function test_admin_can_edit_file_details_and_move_it_to_an_activities_grade_category(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'dni' => 'STUDENT-EDIT-1001',
            'first_name' => 'María',
            'last_name' => 'López',
            'grade' => '3° Secundaria',
        ]);
        $file = $this->createStoredFile($admin, [
            'name' => 'actividad-anterior.pdf',
            'path' => StoredFileSections::directory('Plan_Estrategico_IJENT').'/actividad-anterior.pdf',
            'section' => 'Plan_Estrategico_IJENT',
            'file_type' => 'document',
            'visibility' => 'private',
            'description' => 'Descripción anterior',
        ]);
        Storage::disk('local')->put($file->path, 'contenido');

        $this->actingAs($admin)->get(route('admin.files.edit', $file->id))
            ->assertOk()
            ->assertSee('Editar archivo')
            ->assertSee('Reemplazar archivo')
            ->assertSee('Guardar cambios');

        $this->actingAs($admin)->put(route('admin.files.update', $file->id), [
            'name' => 'Actividad revisada.pdf',
            'description' => 'Descripción actualizada',
            'section' => 'Actividades_Extracurriculares',
            'subfolder' => '3° Secundaria',
            'course_id' => $this->courseIds['Comunicación'],
            'student_id' => $student->id,
            'grade' => '3°',
            'file_type' => 'document',
            'visibility' => 'published',
            'available_from' => today()->toDateString(),
            'available_until' => today()->addDays(10)->toDateString(),
        ])->assertRedirect(route('admin.files.panel'));

        $file->refresh();
        $this->assertSame('Actividad revisada.pdf', $file->name);
        $this->assertSame('Descripción actualizada', $file->description);
        $this->assertSame('Actividades_Extracurriculares', $file->section);
        $this->assertSame($student->id, $file->student_id);
        $this->assertSame($this->courseIds['Comunicación'], $file->course_id);
        $this->assertSame('published', $file->visibility);
        $this->assertStringStartsWith(
            StoredFileSections::directory(
                'Actividades_Extracurriculares',
                '3° Secundaria',
                'document',
                $this->courseIds['Comunicación']
            ).'/',
            $file->path
        );
        Storage::disk('local')->assertExists($file->path);
        Storage::disk('local')->assertMissing(StoredFileSections::directory('Plan_Estrategico_IJENT').'/actividad-anterior.pdf');
    }

    public function test_admin_can_replace_a_file_without_removing_it_when_no_replacement_is_submitted(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $file = $this->createStoredFile($admin, [
            'name' => 'original.pdf',
            'path' => StoredFileSections::directory('Plan_Estrategico_IJENT').'/original.pdf',
            'section' => 'Plan_Estrategico_IJENT',
            'file_type' => 'document',
        ]);
        Storage::disk('local')->put($file->path, 'original');

        $this->actingAs($admin)->put(route('admin.files.update', $file->id), [
            'name' => 'reemplazado.pdf',
            'description' => '',
            'section' => 'Plan_Estrategico_IJENT',
            'file_type' => 'document',
            'visibility' => 'private',
        ])->assertRedirect(route('admin.files.panel'));

        $file->refresh();
        $this->assertSame('reemplazado.pdf', $file->name);
        Storage::disk('local')->assertExists($file->path);

        $this->put(route('admin.files.update', $file->id), [
            'name' => 'reemplazado.pdf',
            'description' => '',
            'section' => 'Plan_Estrategico_IJENT',
            'file_type' => 'document',
            'visibility' => 'private',
            'file' => UploadedFile::fake()->create('nuevo.pdf', 18, 'application/pdf'),
        ])->assertRedirect(route('admin.files.panel'));

        $file->refresh();
        $this->assertSame(18 * 1024, $file->size);
        Storage::disk('local')->assertExists($file->path);
        Storage::disk('local')->assertMissing(StoredFileSections::directory('Plan_Estrategico_IJENT').'/original.pdf');
    }

    public function test_admin_cannot_assign_a_subfolder_to_a_section_without_subfolders(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('plan.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'section' => 'Plan_Estrategico_IJENT',
            'subfolder' => '2° Secundaria',
        ])->assertSessionHasErrors('subfolder');

        $this->assertDatabaseCount('stored_files', 0);
    }

    public function test_admin_cannot_upload_to_a_project_section_that_is_not_in_the_submenu(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.files.store'), [
            'file' => UploadedFile::fake()->create('general.pdf', 10, 'application/pdf'),
            'visibility' => 'published',
            'file_type' => 'auto',
            'section' => 'Proyecto_IJENT',
        ])->assertSessionHasErrors('section');

        $this->assertDatabaseCount('stored_files', 0);
    }

    public function test_changing_a_file_section_moves_it_to_the_selected_folder_without_overwriting(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $file = $this->createStoredFile($admin, [
            'path' => 'files/guia.pdf',
            'disk' => 'local',
        ]);
        $existingPath = StoredFileSections::directory('Recursos_Pedagogicos').'/guia.pdf';
        Storage::disk('local')->put($existingPath, 'existing');

        $this->actingAs($admin)
            ->post(route('admin.files.update_section', $file->id), [
                'section' => 'Recursos_Pedagogicos',
            ])
            ->assertRedirect();

        $file->refresh();
        $this->assertSame('Recursos_Pedagogicos', $file->section);
        $this->assertSame(StoredFileSections::directory('Recursos_Pedagogicos').'/guia-1.pdf', $file->path);
        Storage::disk('local')->assertMissing('files/guia.pdf');
        Storage::disk('local')->assertExists($existingPath);
        Storage::disk('local')->assertExists($file->path);
    }

    public function test_admin_can_upload_and_play_a_private_media_library_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.media-library.store'), [
            'file' => UploadedFile::fake()->create('presentacion.mp4', 10, 'video/mp4'),
        ]);

        $response->assertRedirect();
        $file = StoredFile::query()->firstOrFail();
        $this->assertSame('local', $file->disk);
        $this->assertSame(StoredFile::MEDIA_LIBRARY_SECTION, $file->section);
        $this->assertSame('private', $file->visibility);
        $this->assertStringStartsWith(StoredFileSections::directory(StoredFile::MEDIA_LIBRARY_SECTION).'/', $file->path);
        Storage::disk('local')->assertExists($file->path);

        $this->get(route('admin.media-library.index'))->assertOk()->assertSee('presentacion.mp4');
        $this->get(route('admin.media-library.preview', $file->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'video/mp4');
        $this->get(route('admin.files.panel'))->assertDontSee('presentacion.mp4');
        $this->get(route('public.files.show', $file->id))->assertNotFound();
    }

    public function test_media_library_files_cannot_be_published_or_moved_into_public_sections(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $file = $this->createStoredFile($admin, [
            'section' => StoredFile::MEDIA_LIBRARY_SECTION,
            'visibility' => 'private',
            'path' => 'biblioteca-multimedia/privado.pdf',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.files.publish', $file->id))
            ->assertNotFound();
        $this->post(route('admin.files.update_section', $file->id), [
            'section' => 'Proyecto_IJENT',
        ])->assertNotFound();
        $this->get(route('admin.files.preview', $file->id))->assertNotFound();
        $this->get(route('public.files.show', $file->id))->assertNotFound();

        $this->assertSame('private', $file->fresh()->visibility);
        $this->assertSame(StoredFile::MEDIA_LIBRARY_SECTION, $file->fresh()->section);
    }

    public function test_media_library_delete_removes_only_the_private_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $file = $this->createStoredFile($admin, [
            'section' => StoredFile::MEDIA_LIBRARY_SECTION,
            'visibility' => 'private',
            'path' => 'biblioteca-multimedia/privado.pdf',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.media-library.destroy', $file->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('stored_files', ['id' => $file->id]);
        Storage::disk('local')->assertMissing($file->path);
    }

    public function test_public_feed_and_file_endpoint_enforce_visibility_and_publication_dates(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $today = today(config('school.timezone', 'America/Lima'));

        $active = $this->createStoredFile($owner, [
            'visibility' => 'published',
            'available_from' => $today->toDateString(),
            'available_until' => $today->toDateString(),
        ]);
        $future = $this->createStoredFile($owner, [
            'visibility' => 'published',
            'available_from' => $today->copy()->addDay()->toDateString(),
        ]);
        $expired = $this->createStoredFile($owner, [
            'visibility' => 'published',
            'available_until' => $today->copy()->subDay()->toDateString(),
        ]);
        $private = $this->createStoredFile($owner, ['visibility' => 'private']);

        $feed = $this->getJson(route('drive.local_project', ['section' => 'Proyecto_IJENT']));
        $feed->assertOk()->assertJsonCount(1, 'files');
        $feed->assertJsonPath('files.0.id', 'local-'.$active->id);

        $this->get(route('public.files.show', $active->id))->assertOk();
        $this->get(route('public.files.show', $future->id))->assertNotFound();
        $this->get(route('public.files.show', $expired->id))->assertNotFound();
        $this->get(route('public.files.show', $private->id))->assertNotFound();
    }

    public function test_published_audio_is_listed_for_its_project_subfolder_and_served_inline(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $courseId = $this->courseIds['Matemática'];
        $file = $this->createStoredFile($owner, [
            'name' => 'audio-proyecto.mp3',
            'description' => 'Audio de la actividad escolar',
            'path' => StoredFileSections::directory(
                'Actividades_Extracurriculares',
                '2° Secundaria',
                'audio',
                $courseId
            ).'/audio-proyecto.mp3',
            'mime' => 'audio/mpeg',
            'file_type' => 'audio',
            'section' => 'Actividades_Extracurriculares',
            'course_id' => $courseId,
        ]);

        $this->getJson(route('drive.local_project', [
            'section' => 'Actividades_Extracurriculares',
            'folder' => '2° Secundaria',
            'course' => $courseId,
            'category' => 'audio',
        ]))
            ->assertOk()
            ->assertJsonPath('files.0.name', 'audio-proyecto.mp3')
            ->assertJsonPath('files.0.mimeType', 'audio/mpeg')
            ->assertJsonPath('files.0.description', 'Audio de la actividad escolar');

        $response = $this->get(route('public.files.show', $file->id));
        $response->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringNotContainsString(
            'attachment',
            $response->headers->get('Content-Disposition', '')
        );
    }

    public function test_legacy_audio_saved_directly_in_a_grade_folder_remains_visible_and_playable(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $gradeDirectory = StoredFileSections::directory('Actividades_Extracurriculares', '1° Secundaria');
        $audio = $this->createStoredFile($owner, [
            'name' => 'audio-anterior.mp3',
            'path' => $gradeDirectory.'/audio-anterior.mp3',
            'mime' => 'audio/mpeg',
            'file_type' => 'audio',
            'section' => 'Actividades_Extracurriculares',
        ]);

        $this->getJson(route('drive.local_project', [
            'section' => 'Actividades_Extracurriculares',
            'folder' => '1° Secundaria',
        ]))
            ->assertOk()
            ->assertJsonFragment(['name' => 'audio-anterior.mp3'])
            ->assertJsonFragment(['mimeType' => 'audio/mpeg'])
            ->assertJsonFragment(['localUrl' => route('public.files.show', $audio->id)]);

        $this->get(route('public.files.show', $audio->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'audio/mpeg');
    }

    public function test_project_root_video_is_available_in_the_existing_project_view_without_creating_a_new_menu_folder(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $video = $this->createStoredFile($owner, [
            'name' => 'video-proyecto.mp4',
            'path' => StoredFileSections::directory('Proyecto_IJENT').'/video-proyecto.mp4',
            'mime' => 'video/mp4',
            'file_type' => 'video',
            'section' => 'Proyecto_IJENT',
        ]);

        $this->getJson(route('drive.local_project', [
            'section' => 'Proyecto_IJENT',
        ]))
            ->assertOk()
            ->assertJsonPath('files.0.name', 'video-proyecto.mp4')
            ->assertJsonPath('files.0.mimeType', 'video/mp4');

        $this->getJson(route('drive.local_project'))
            ->assertOk()
            ->assertJsonMissing(['localSection' => 'Proyecto_IJENT']);

        $response = $this->get(route('public.files.show', $video->id));
        $response->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringNotContainsString(
            'attachment',
            $response->headers->get('Content-Disposition', '')
        );
    }

    private function createStoredFile(User $owner, array $overrides = []): StoredFile
    {
        $section = $overrides['section'] ?? 'Proyecto_IJENT';
        $attributes = array_merge([
            'user_id' => $owner->id,
            'name' => 'recurso.pdf',
            'path' => StoredFileSections::directory($section).'/recurso.pdf',
            'disk' => 'local',
            'mime' => 'application/pdf',
            'size' => 4,
            'visibility' => 'published',
            'published_at' => now(),
            'section' => 'Proyecto_IJENT',
        ], $overrides);

        Storage::disk('local')->put($attributes['path'], 'PDF');

        return StoredFile::create($attributes);
    }
}
