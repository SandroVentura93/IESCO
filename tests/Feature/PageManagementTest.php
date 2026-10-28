<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteSectionSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_a_page_in_a_section_and_see_it_on_the_public_site(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Programa de becas',
            'slug' => 'programa-de-becas',
            'section' => 'novedades',
            'content' => '<p>Información <strong>importante</strong>.</p><script>alert(1)</script><img src=x onerror=alert(1)>',
            'position' => 2,
            'published' => '1',
            'featured_image' => UploadedFile::fake()->createWithContent(
                'becas.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/e/8AAAAASUVORK5CYII=')
            ),
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $page = Page::where('slug', 'programa-de-becas')->firstOrFail();
        $this->assertTrue($page->published);
        $this->assertSame('novedades', $page->section);
        $this->assertStringNotContainsString('<script', $page->content);
        $this->assertStringNotContainsString('onerror', $page->content);
        Storage::disk('public')->assertExists($page->featured_image);

        $this->actingAs($admin);
        $this->get(route('admin.pages.index', ['section' => 'novedades']))
            ->assertOk()
            ->assertSee('Programa de becas')
            ->assertSee('Novedades');
        $this->get(route('admin.pages.edit', $page))
            ->assertOk()
            ->assertSee('Guardar cambios')
            ->assertSee('Publicar esta página');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Programa de becas')
            ->assertSee('Más páginas')
            ->assertSee(route('pages.show', $page->slug));
        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('<strong>importante</strong>', false)
            ->assertDontSee('alert(1)');
    }

    public function test_homepage_renders_news_cards_in_a_responsive_three_at_a_time_carousel(): void
    {
        $response = $this->get(route('home'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(6, substr_count($html, 'class="news-grid-item"'));
        $this->assertSame(6, substr_count($html, 'class="news-card"'));
        $this->assertStringContainsString('id="novedadesGrid"', $html);
        $this->assertStringContainsString('id="novedadesPrev"', $html);
        $this->assertStringContainsString('id="novedadesNext"', $html);
        $this->assertStringContainsString('id="novedadesStatus"', $html);
        $this->assertStringNotContainsString('class="glide__slides news-grid"', $html);
    }

    public function test_homepage_uses_school_photos_in_the_matching_sections(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(asset('images/comunidad-educativa.jpg'), false)
            ->assertSee(asset('images/exposicion-estudiantil.jpg'), false)
            ->assertSee(asset('images/formacion-docente.jpg'), false)
            ->assertSee(asset('images/encuentro-con-familias.jpg'), false)
            ->assertSee(asset('images/salida-pedagogica.jpg'), false)
            ->assertSee(asset('images/aprendizaje-en-el-aula.jpg'), false)
            ->assertSee(asset('images/equipo-estudiantil.jpg'), false)
            ->assertSee('educación secundaria')
            ->assertSee('estudiantes de secundaria')
            ->assertSee('Proyectos de estudiantes')
            ->assertSee('videos.pexels.com');
    }

    public function test_admin_can_change_a_page_section_and_unpublish_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::create([
            'title' => 'Guía de matrícula',
            'slug' => 'guia-de-matricula',
            'section' => 'institucional',
            'content' => '<p>Fechas y requisitos.</p>',
            'published' => true,
            'position' => 1,
        ]);

        $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'title' => 'Guía de matrícula actualizada',
            'slug' => 'guia-de-matricula',
            'section' => 'publicaciones',
            'content' => '<p>Nueva información.</p>',
            'position' => 3,
        ])->assertRedirect(route('admin.pages.edit', $page));

        $page->refresh();
        $this->assertSame('publicaciones', $page->section);
        $this->assertSame(3, $page->position);
        $this->assertFalse($page->published);
        $this->get(route('pages.show', $page->slug))->assertNotFound();
        $this->get(route('home'))->assertDontSee('Guía de matrícula actualizada');
    }

    public function test_public_page_route_never_exposes_a_draft(): void
    {
        $page = Page::create([
            'title' => 'Borrador interno',
            'slug' => 'borrador-interno',
            'section' => 'institucional',
            'content' => 'Solo administración.',
            'published' => false,
        ]);

        $this->get(route('pages.show', $page->slug))->assertNotFound();
    }

    public function test_admin_can_customize_every_homepage_hero_text_and_video(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.pages.create', ['homepage' => 1]))
            ->assertOk()
            ->assertSee('Diseño de la portada')
            ->assertSee('inicio_video_file')
            ->assertSee('inicio_students_value');

        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Inicio',
            'slug' => 'inicio',
            'section' => 'inicio',
            'inicio_badge' => 'Bienvenidos a Tumbadén',
            'inicio_title_prefix' => 'Institución',
            'inicio_title_highlight' => 'SCO 2026',
            'inicio_institution_name' => 'Comunidad educativa renovada',
            'inicio_motto' => 'Aprender para transformar',
            'inicio_subtitle' => 'Educación con oportunidades para todos.',
            'inicio_left_button_text' => 'Conoce nuestras noticias',
            'inicio_left_button_url' => '#novedades',
            'inicio_right_button_text' => 'Conócenos hoy',
            'inicio_right_button_url' => '#quienes-somos',
            'inicio_students_value' => 350,
            'inicio_students_label' => 'Estudiantes activos',
            'inicio_teachers_value' => 24,
            'inicio_teachers_label' => 'Docentes comprometidos',
            'inicio_years_value' => 40,
            'inicio_years_label' => 'Años educando',
            'inicio_projects_value' => 8,
            'inicio_projects_label' => 'Proyectos vigentes',
            'inicio_video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
            'published' => '1',
        ])->assertRedirect(route('admin.pages.index'));

        $this->assertSame('Bienvenidos a Tumbadén', Setting::get('inicio_badge'));
        $this->assertSame('https://www.youtube.com/watch?v=aqz-KE-bpKQ', Setting::get('inicio_video_url'));
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Bienvenidos a Tumbadén')
            ->assertSee('SCO 2026')
            ->assertSee('Comunidad educativa renovada')
            ->assertSee('Aprender para transformar')
            ->assertSee('Educación con oportunidades para todos.')
            ->assertSee('Conoce nuestras noticias')
            ->assertSee('Estudiantes activos')
            ->assertSee('350')
            ->assertSee('https://www.youtube-nocookie.com/embed/aqz-KE-bpKQ');
    }

    public function test_admin_can_upload_a_homepage_background_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Inicio',
            'slug' => 'inicio',
            'section' => 'inicio',
            'inicio_image' => UploadedFile::fake()->createWithContent(
                'portada.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/e/8AAAAASUVORK5CYII=')
            ),
        ])->assertRedirect(route('admin.pages.index'));

        $imageUrl = Setting::get('inicio_image');
        $this->assertStringContainsString('/storage/settings/home/', $imageUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($imageUrl, PHP_URL_PATH)));
        $this->get(route('home'))->assertOk()->assertSee($imageUrl);
    }

    public function test_admin_can_upload_a_homepage_video_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Inicio',
            'slug' => 'inicio',
            'section' => 'inicio',
            'inicio_video_file' => UploadedFile::fake()->create('presentacion.mp4', 10, 'video/mp4'),
        ])->assertRedirect(route('admin.pages.index'));

        $videoUrl = Setting::get('inicio_video');
        $this->assertStringContainsString('/storage/settings/home/', $videoUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($videoUrl, PHP_URL_PATH)));
        $this->get(route('home'))->assertOk()->assertSee($videoUrl)->assertSee('video/mp4');
    }

    public function test_admin_can_edit_project_section_content_and_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $definition = SiteSectionSettings::definitions()['proyecto'];
        $payload = collect($definition['fields'])
            ->map(fn (array $field) => $field['default'])
            ->all();
        $payload['title'] = 'Proyecto IJENT renovado';
        $payload['highlight_description'] = 'Conoce las experiencias educativas actualizadas.';
        $payload['media_image'] = UploadedFile::fake()->createWithContent(
            'ijent.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/e/8AAAAASUVORK5CYII=')
        );

        $this->actingAs($admin)
            ->get(route('admin.pages.sections.edit', 'proyecto'))
            ->assertOk()
            ->assertSee('Editar Proyecto IJENT')
            ->assertSee('Descripción de la tarjeta');

        $this->actingAs($admin)
            ->put(route('admin.pages.sections.update', 'proyecto'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'proyecto'));

        $imageUrl = Setting::get('site_section_proyecto_image');
        $this->assertStringContainsString('/storage/settings/sections/proyecto/', $imageUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($imageUrl, PHP_URL_PATH)));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Proyecto IJENT renovado')
            ->assertSee('Conoce las experiencias educativas actualizadas.')
            ->assertSee($imageUrl);
    }

    public function test_admin_sidebar_has_searchable_accessible_navigation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.pages.sections.edit', 'publicaciones'))
            ->assertOk()
            ->assertSee('id="adminSidebar"', false)
            ->assertSee('id="adminSidebarMobileToggle"', false)
            ->assertSee('id="adminSidebarSearch"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('No hay opciones que coincidan.');
    }

    public function test_admin_can_customize_each_main_site_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $definitions = SiteSectionSettings::definitions();

        $this->actingAs($admin)
            ->get(route('admin.pages.sections.edit', 'publicaciones'))
            ->assertOk()
            ->assertSee('Imagen de publicación 1')
            ->assertSee('name="media_slide_1_image"', false)
            ->assertSee('name="media_slide_2_image"', false)
            ->assertSee('name="media_slide_3_image"', false)
            ->assertDontSee('Imágenes de la sección');

        foreach ($definitions as $section => $definition) {
            if ($section === 'footer') {
                continue;
            }

            $this->actingAs($admin)
                ->get(route('admin.pages.sections.edit', $section))
                ->assertOk()
                ->assertSee('Editar '.$definition['label']);

            $payload = collect($definition['fields'])
                ->map(fn (array $field) => $field['default'])
                ->all();
            $payload['title'] = $definition['label'].' personalizado';
            if ($section === 'novedades') {
                $payload['news_1_title'] = 'Noticia editable de prueba';
                $payload['news_1_icon'] = 'fa-bullhorn';
            }
            if ($section === 'publicaciones') {
                $payload['slide_1_title'] = 'Publicación editable de prueba';
            }
            if ($section === 'quienes-somos') {
                $payload['mission'] = 'Misión editable de prueba.';
                $payload['feature_academic_icon'] = 'fa-graduation-cap';
                $payload['mission_icon'] = 'fa-lightbulb';
            }
            if ($section === 'proyecto') {
                $payload['pill_1_icon'] = 'fa-book-open';
                $payload['highlight_tag_icon'] = 'fa-trophy';
            }

            $this->put(route('admin.pages.sections.update', $section), $payload)
                ->assertRedirect(route('admin.pages.sections.edit', $section))
                ->assertSessionHasNoErrors();
            $this->assertSame(
                $payload['title'],
                Setting::get(SiteSectionSettings::settingKey($section, 'title')),
                'Section title was not persisted for '.$section.'.'
            );
        }

        $home = $this->get(route('home'))->assertOk();
        foreach ($definitions as $section => $definition) {
            if ($section !== 'footer') {
                $home->assertSee($definition['label'].' personalizado');
            }
        }
        $home->assertSee('Noticia editable de prueba')
            ->assertSee('Publicación editable de prueba')
            ->assertSee('Misión editable de prueba.')
            ->assertSee('fas fa-bullhorn', false)
            ->assertSee('fas fa-graduation-cap', false)
            ->assertSee('fas fa-lightbulb', false)
            ->assertSee('fas fa-book-open', false)
            ->assertSee('fas fa-trophy', false);
    }

    public function test_publication_slides_can_be_scheduled_and_hidden_outside_their_date_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.pages.sections.edit', 'publicaciones'))
            ->assertOk()
            ->assertSee('name="slide_1_starts_at"', false)
            ->assertSee('name="slide_1_ends_at"', false)
            ->assertSee('name="slide_3_starts_at"', false)
            ->assertSee('name="slide_3_ends_at"', false);

        $definition = SiteSectionSettings::definitions()['publicaciones'];
        $payload = collect($definition['fields'])
            ->map(fn (array $field) => $field['default'])
            ->all();
        $payload['slide_1_title'] = 'Publicación programada';
        $payload['slide_1_starts_at'] = '2026-10-24';
        $payload['slide_1_ends_at'] = '2026-10-24';

        $invalidDates = $payload;
        $invalidDates['slide_1_starts_at'] = '2026-10-25';
        $this->from(route('admin.pages.sections.edit', 'publicaciones'))
            ->put(route('admin.pages.sections.update', 'publicaciones'), $invalidDates)
            ->assertRedirect(route('admin.pages.sections.edit', 'publicaciones'))
            ->assertSessionHasErrors('slide_1_ends_at');

        $this->put(route('admin.pages.sections.update', 'publicaciones'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'publicaciones'));

        $this->assertSame('2026-10-24', Setting::get(SiteSectionSettings::settingKey('publicaciones', 'slide_1_starts_at')));
        $this->assertSame('2026-10-24', Setting::get(SiteSectionSettings::settingKey('publicaciones', 'slide_1_ends_at')));

        Carbon::setTestNow('2026-10-24 12:00:00');
        try {
            $this->get(route('home'))
                ->assertOk()
                ->assertSee('Publicación programada')
                ->assertSee('1 de 3');

            Carbon::setTestNow('2026-10-25 12:00:00');
            $this->get(route('home'))
                ->assertOk()
                ->assertDontSee('Publicación programada')
                ->assertSee('Lectura y aprendizaje en el aula');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_admin_can_add_and_delete_publication_slides_with_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.pages.sections.edit', 'publicaciones'))
            ->assertOk()
            ->assertSee('id="addPublicationItem"', false)
            ->assertSee('id="publicationAddModal"', false)
            ->assertSee('data-remove-publication', false)
            ->assertSee('name="publication_count"', false)
            ->assertSee('data-modal-publication-field="icon"', false)
            ->assertSee('Naturaleza')
            ->assertSee('Comunicados')
            ->assertSee('data-publication-field="starts_at"', false)
            ->assertSee('data-publication-field="ends_at"', false);

        $definition = SiteSectionSettings::definitions()['publicaciones'];
        $payload = collect($definition['fields'])
            ->map(fn (array $field) => $field['default'])
            ->all();
        $payload['publication_count'] = 4;
        $payload['slide_4_title'] = 'Publicación adicional';
        $payload['slide_4_description'] = 'Contenido adicional del carrusel.';
        $payload['slide_4_icon'] = 'fa-trophy';
        $payload['slide_4_starts_at'] = '2026-10-24';
        $payload['slide_4_ends_at'] = '2026-10-26';
        $payload['media_slide_4_image'] = UploadedFile::fake()->createWithContent(
            'publication-four.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/e/8AAAAASUVORK5CYII=')
        );

        $invalidDates = $payload;
        $invalidDates['slide_4_starts_at'] = '2026-10-27';
        $this->from(route('admin.pages.sections.edit', 'publicaciones'))
            ->put(route('admin.pages.sections.update', 'publicaciones'), $invalidDates)
            ->assertRedirect(route('admin.pages.sections.edit', 'publicaciones'))
            ->assertSessionHasErrors('slide_4_ends_at');

        $invalidIcon = $payload;
        $invalidIcon['slide_4_icon'] = 'fa-not-an-approved-icon';
        $this->from(route('admin.pages.sections.edit', 'publicaciones'))
            ->put(route('admin.pages.sections.update', 'publicaciones'), $invalidIcon)
            ->assertRedirect(route('admin.pages.sections.edit', 'publicaciones'))
            ->assertSessionHasErrors('slide_4_icon');

        $this->put(route('admin.pages.sections.update', 'publicaciones'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'publicaciones'));

        $imageUrl = Setting::get(SiteSectionSettings::settingKey('publicaciones', 'slide_4_image'));
        $this->assertSame('4', Setting::get(SiteSectionSettings::settingKey('publicaciones', 'publication_count')));
        $this->assertNotEmpty($imageUrl);
        $imagePath = str_replace('/storage/', '', parse_url($imageUrl, PHP_URL_PATH));
        Storage::disk('public')->assertExists($imagePath);
        Carbon::setTestNow('2026-10-24 12:00:00');
        try {
            $this->get(route('home'))
                ->assertOk()
                ->assertSee('Publicación adicional')
                ->assertSee('4 de 4')
                ->assertSee('fas fa-trophy', false)
                ->assertSee($imageUrl);
        } finally {
            Carbon::setTestNow();
        }

        unset($payload['media_slide_4_image']);
        $payload['publication_count'] = 3;
        $payload['publication_deleted'] = [4];
        $this->put(route('admin.pages.sections.update', 'publicaciones'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'publicaciones'));
        $this->assertSame('', Setting::get(SiteSectionSettings::settingKey('publicaciones', 'slide_4_title')));
        $this->assertSame('', Setting::get(SiteSectionSettings::settingKey('publicaciones', 'slide_4_icon')));
        $this->assertSame('', Setting::get(SiteSectionSettings::settingKey('publicaciones', 'slide_4_image')));
        Storage::disk('public')->assertMissing($imagePath);
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Publicación adicional');
    }

    public function test_admin_can_add_scheduled_news_cards(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.pages.sections.edit', 'novedades'))
            ->assertOk()
            ->assertSee('id="addNewsItem"', false)
            ->assertSee('id="newsAddModal"', false)
            ->assertSee('data-remove-news', false)
            ->assertSee('name="news_1_icon"', false)
            ->assertSee('data-modal-news-field="icon"', false)
            ->assertSee('name="news_1_starts_at"', false)
            ->assertSee('name="news_1_ends_at"', false)
            ->assertSee('name="news_count"', false);

        $definition = SiteSectionSettings::definitions()['novedades'];
        $payload = collect($definition['fields'])
            ->map(fn (array $field) => $field['default'])
            ->all();
        $payload['news_count'] = 7;
        $payload['news_7_date'] = '24 de octubre de 2026';
        $payload['news_7_title'] = 'Novedad programada';
        $payload['news_7_excerpt'] = 'Visible dentro del periodo configurado.';
        $payload['news_7_tag'] = 'Institucional';
        $payload['news_7_icon'] = 'fa-bullhorn';
        $payload['news_7_starts_at'] = '2026-10-24';
        $payload['news_7_ends_at'] = '2026-10-24';
        $payload['media_news_7_image'] = UploadedFile::fake()->createWithContent(
            'news-seven.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/e/8AAAAASUVORK5CYII=')
        );

        $invalidDates = $payload;
        $invalidDates['news_7_starts_at'] = '2026-10-25';
        $this->from(route('admin.pages.sections.edit', 'novedades'))
            ->put(route('admin.pages.sections.update', 'novedades'), $invalidDates)
            ->assertRedirect(route('admin.pages.sections.edit', 'novedades'))
            ->assertSessionHasErrors('news_7_ends_at');

        $this->put(route('admin.pages.sections.update', 'novedades'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'novedades'));

        $this->assertSame('7', Setting::get(SiteSectionSettings::settingKey('novedades', 'news_count')));
        $this->assertSame('2026-10-24', Setting::get(SiteSectionSettings::settingKey('novedades', 'news_7_starts_at')));
        $newsImageUrl = Setting::get(SiteSectionSettings::settingKey('novedades', 'news_7_image'));
        $this->assertNotEmpty($newsImageUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($newsImageUrl, PHP_URL_PATH)));

        Carbon::setTestNow('2026-10-24 12:00:00');
        try {
            $visibleNews = $this->get(route('home'))
                ->assertOk()
                ->assertSee('Novedad programada')
                ->assertSee('Visible dentro del periodo configurado.')
                ->assertSee('fas fa-bullhorn', false);
            $this->assertSame(7, substr_count($visibleNews->getContent(), 'class="news-grid-item"'));

            Carbon::setTestNow('2026-10-25 12:00:00');
            $expiredNews = $this->get(route('home'))
                ->assertOk()
                ->assertDontSee('Novedad programada');
            $this->assertSame(6, substr_count($expiredNews->getContent(), 'class="news-grid-item"'));
        } finally {
            Carbon::setTestNow();
        }

        unset($payload['media_news_7_image']);
        $payload['news_count'] = 6;
        $payload['news_deleted'] = [7];
        $this->put(route('admin.pages.sections.update', 'novedades'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'novedades'));
        $this->assertSame('', Setting::get(SiteSectionSettings::settingKey('novedades', 'news_7_title')));
        $this->assertSame('', Setting::get(SiteSectionSettings::settingKey('novedades', 'news_7_image')));
        Storage::disk('public')->assertMissing(str_replace('/storage/', '', parse_url($newsImageUrl, PHP_URL_PATH)));
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Novedad programada');

        unset($payload['news_deleted']);
        $payload['news_deleted'] = [2];
        $this->put(route('admin.pages.sections.update', 'novedades'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'novedades'));
        $this->assertSame('', Setting::get(SiteSectionSettings::settingKey('novedades', 'news_2_title')));
        $this->assertSame(5, substr_count($this->get(route('home'))->assertOk()->getContent(), 'class="news-grid-item"'));
    }

    public function test_section_settings_remain_visible_with_a_custom_about_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Page::create([
            'title' => 'Historia ampliada',
            'slug' => 'quienes-somos',
            'section' => 'quienes-somos',
            'content' => '<p>Contenido institucional personalizado.</p>',
            'published' => true,
        ]);
        $definition = SiteSectionSettings::definitions()['quienes-somos'];
        $payload = collect($definition['fields'])
            ->map(fn (array $field) => $field['default'])
            ->all();
        $payload['title'] = 'Conoce nuestra institución';
        $payload['mission'] = 'Misión actualizada para la comunidad.';

        $this->actingAs($admin)
            ->put(route('admin.pages.sections.update', 'quienes-somos'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'quienes-somos'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Conoce nuestra institución')
            ->assertSee('Misión actualizada para la comunidad.')
            ->assertSee('Contenido institucional personalizado.');
    }

    public function test_admin_can_customize_the_global_footer(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $definition = SiteSectionSettings::definitions()['footer'];
        $payload = collect($definition['fields'])
            ->map(fn (array $field) => $field['default'])
            ->all();
        $payload['short_name'] = 'IJENT Institución';
        $payload['navigation_title'] = 'Explora el sitio';
        $payload['nav_1_label'] = 'Página principal';
        $payload['nav_1_url'] = '#inicio';
        $payload['email'] = 'contacto@ijent.edu.pe';
        $payload['social_facebook'] = 'https://facebook.com/ijent';
        $payload['media_logo'] = UploadedFile::fake()->createWithContent(
            'footer.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/e/8AAAAASUVORK5CYII=')
        );

        $this->actingAs($admin)
            ->get(route('admin.pages.sections.edit', 'footer'))
            ->assertOk()
            ->assertSee('Editar Pie de página')
            ->assertSee('Enlace 1 · destino');

        $invalidPayload = $payload;
        $invalidPayload['social_facebook'] = 'javascript:alert(1)';
        $this->put(route('admin.pages.sections.update', 'footer'), $invalidPayload)
            ->assertSessionHasErrors('social_facebook');

        $this->put(route('admin.pages.sections.update', 'footer'), $payload)
            ->assertRedirect(route('admin.pages.sections.edit', 'footer'))
            ->assertSessionHasNoErrors();

        $logoUrl = Setting::get('site_section_footer_logo');
        $this->assertStringContainsString('/storage/settings/sections/footer/', $logoUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($logoUrl, PHP_URL_PATH)));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('IJENT Institución')
            ->assertSee('Explora el sitio')
            ->assertSee(route('home').'#inicio')
            ->assertSee('contacto@ijent.edu.pe')
            ->assertSee('https://facebook.com/ijent')
            ->assertSee($logoUrl);
    }
}
