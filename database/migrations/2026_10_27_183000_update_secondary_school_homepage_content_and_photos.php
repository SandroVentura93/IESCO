<?php

use App\Support\SiteSectionSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BACKUP_TABLE = 'secondary_school_homepage_settings_backup';

    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $updates = [
            'inicio_subtitle' => 'Acompañamos a adolescentes en su educación secundaria, fortaleciendo sus aprendizajes, valores y proyectos de vida.',
        ];

        $about = [
            'eyebrow' => 'Educación secundaria en Tumbadén',
            'intro_heading' => 'Institución Educativa Secundaria "Salomón Cáceres Oliva" — Tumbadén',
            'intro_first' => 'Somos una institución educativa pública de nivel secundario ubicada en Tumbadén, distrito de San Pablo, región Cajamarca — Perú. Acompañamos a adolescentes en su formación académica y personal, promoviendo el pensamiento crítico, la autonomía y el compromiso con su comunidad.',
            'intro_second' => 'Nuestra comunidad reúne a estudiantes, docentes y familias para construir aprendizajes sólidos, fortalecer la convivencia y preparar a los jóvenes para continuar sus estudios y participar activamente en la sociedad.',
            'motto_caption' => 'Valores que guían nuestra formación secundaria',
            'feature_academic' => 'Aprendizaje y pensamiento crítico',
            'feature_innovation' => 'Proyectos y aprendizaje aplicado',
            'feature_community' => 'Comunidad escolar secundaria',
            'inline_caption' => 'Familias y docentes se reúnen para acompañar la formación de los estudiantes.',
            'main_caption' => 'Estudiantes, docentes y familias de nuestra institución secundaria.',
            'mission' => 'Brindar educación secundaria integral e inclusiva que fortalezca los aprendizajes, los valores y las competencias de los adolescentes, en colaboración con sus familias y la comunidad de Tumbadén.',
            'vision' => 'Ser una institución secundaria rural reconocida en la provincia de San Pablo por los aprendizajes de sus estudiantes, la convivencia respetuosa y el compromiso con su comunidad.',
            'values' => 'Promovemos el respeto, la responsabilidad, la honestidad, el esfuerzo y la colaboración entre estudiantes, docentes y familias.',
        ];

        foreach ($about as $field => $value) {
            $updates[SiteSectionSettings::settingKey('quienes-somos', $field)] = $value;
        }

        $project = [
            'eyebrow' => 'Aprendizajes de secundaria',
            'title' => 'Experiencias de nuestra secundaria',
            'description' => 'Conoce las actividades, proyectos y aprendizajes que forman parte de la vida diaria de estudiantes y docentes de educación secundaria en Tumbadén.',
            'root_label' => 'Experiencias de secundaria',
            'highlight_tag' => 'Proyecto educativo',
            'highlight_title' => 'Aprendizajes que nacen en nuestras aulas',
            'highlight_description' => 'Explora el trabajo de los estudiantes de secundaria, sus proyectos de aula y las experiencias que comparten con docentes y familias.',
            'pill_1' => 'Proyectos de estudiantes',
            'pill_2' => 'Aprendizajes por grado',
            'pill_3' => 'Materiales educativos',
            'explore_hint' => 'Recorre las actividades y materiales educativos organizados para nuestra comunidad secundaria.',
            'drive_loading_text' => 'Cargando experiencias y materiales de secundaria…',
            'drive_fallback_title' => 'Aprendizaje fuera del aula',
            'drive_fallback_description' => 'Estudiantes y docentes exploran el entorno como parte de sus actividades de aprendizaje de secundaria.',
        ];

        foreach ($project as $field => $value) {
            $updates[SiteSectionSettings::settingKey('proyecto', $field)] = $value;
        }

        $updates[SiteSectionSettings::settingKey('novedades', 'description')] = 'Actividades, proyectos y encuentros que muestran el aprendizaje y la vida cotidiana de nuestra institución secundaria.';
        $news = [
            ['Jornada cívica escolar', 'Estudiantes, docentes y familias participan en actividades formativas y de convivencia de nuestra institución secundaria.', 'Comunidad', 'fa-calendar-alt'],
            ['Proyectos de estudiantes', 'Los estudiantes de secundaria presentan sus proyectos y comparten lo aprendido en clase.', 'Académico', 'fa-graduation-cap'],
            ['Formación para nuestra comunidad', 'Docentes y familias fortalecen el acompañamiento y la educación de los adolescentes.', 'Formación', 'fa-lightbulb'],
            ['Encuentro con las familias', 'La comunicación entre familias y docentes acompaña el desarrollo de los estudiantes de secundaria.', 'Familias', 'fa-users'],
            ['Lectura y aprendizaje práctico', 'La lectura, la exploración científica y las actividades de aula enriquecen la formación secundaria.', 'Aprendizaje', 'fa-book-open'],
            ['Aprendizaje fuera del aula', 'Una salida pedagógica permite a los estudiantes de secundaria conocer y explorar su entorno.', 'Experiencias', 'fa-compass'],
        ];

        $updates[SiteSectionSettings::settingKey('novedades', 'news_count')] = count($news);
        foreach ($news as $index => [$title, $description, $tag, $icon]) {
            $number = $index + 1;
            foreach ([
                'title' => $title,
                'excerpt' => $description,
                'tag' => $tag,
                'icon' => $icon,
            ] as $field => $value) {
                $updates[SiteSectionSettings::settingKey('novedades', 'news_'.$number.'_'.$field)] = $value;
            }
        }

        $publications = [
            ['Exploración del entorno local', 'Una experiencia de aprendizaje para que nuestros estudiantes de secundaria conozcan y exploren su entorno.', 'fa-compass'],
            ['Lectura y aprendizaje en el aula', 'Las actividades de lectura, ciencia y exploración fortalecen los aprendizajes de los estudiantes de secundaria.', 'fa-book-open'],
            ['Participación estudiantil', 'Estudiantes de secundaria y docentes comparten sus proyectos y experiencias formativas.', 'fa-users'],
        ];

        $updates[SiteSectionSettings::settingKey('publicaciones', 'publication_count')] = count($publications);
        foreach ($publications as $index => [$title, $description, $icon]) {
            $number = $index + 1;
            foreach ([
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
            ] as $field => $value) {
                $updates[SiteSectionSettings::settingKey('publicaciones', 'slide_'.$number.'_'.$field)] = $value;
            }
        }

        $newsImages = [
            'images/comunidad-educativa.jpg',
            'images/exposicion-estudiantil.jpg',
            'images/formacion-docente.jpg',
            'images/encuentro-con-familias.jpg',
            'images/aprendizaje-en-el-aula.jpg',
            'images/salida-pedagogica.jpg',
        ];
        $publicationImages = [
            'images/salida-pedagogica.jpg',
            'images/aprendizaje-en-el-aula.jpg',
            'images/equipo-estudiantil.jpg',
        ];
        $media = [
            SiteSectionSettings::settingKey('quienes-somos', 'inline_image') => 'images/encuentro-con-familias.jpg',
            SiteSectionSettings::settingKey('quienes-somos', 'main_image') => 'images/comunidad-educativa.jpg',
            SiteSectionSettings::settingKey('proyecto', 'image') => 'images/salida-pedagogica.jpg',
        ];

        for ($number = 1; $number <= 30; $number++) {
            $media[SiteSectionSettings::settingKey('novedades', 'news_'.$number.'_image')] = $newsImages[($number - 1) % count($newsImages)];
            $media[SiteSectionSettings::settingKey('publicaciones', 'slide_'.$number.'_image')] = $publicationImages[($number - 1) % count($publicationImages)];
        }

        foreach ($media as $key => $path) {
            $updates[$key] = $path;
        }

        if (! Schema::hasTable(self::BACKUP_TABLE)) {
            Schema::create(self::BACKUP_TABLE, function (Blueprint $table): void {
                $table->string('key')->primary();
                $table->text('value')->nullable();
                $table->boolean('existed')->default(false);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        DB::transaction(function () use ($updates): void {
            foreach ($updates as $key => $value) {
                $original = DB::table('settings')->where('key', $key)->first();

                DB::table(self::BACKUP_TABLE)->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => $original->value ?? null,
                        'existed' => $original !== null,
                        'created_at' => $original->created_at ?? now(),
                        'updated_at' => $original->updated_at ?? now(),
                    ],
                );

                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => (string) $value,
                        'created_at' => $original->created_at ?? now(),
                        'updated_at' => now(),
                    ],
                );
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::BACKUP_TABLE) || ! Schema::hasTable('settings')) {
            return;
        }

        DB::transaction(function (): void {
            foreach (DB::table(self::BACKUP_TABLE)->get() as $original) {
                if ($original->existed) {
                    DB::table('settings')->updateOrInsert(
                        ['key' => $original->key],
                        [
                            'value' => $original->value,
                            'created_at' => $original->created_at,
                            'updated_at' => $original->updated_at,
                        ],
                    );
                } else {
                    DB::table('settings')->where('key', $original->key)->delete();
                }
            }
        });

        Schema::dropIfExists(self::BACKUP_TABLE);
    }
};
