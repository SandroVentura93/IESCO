<?php

use App\Support\SiteSectionSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BACKUP_TABLE = 'secondary_school_news_backup';

    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $news = [
            [
                'date' => '7 de julio de 2026',
                'title' => 'Proyectos de aula en secundaria',
                'excerpt' => 'Estudiantes presentan materiales y actividades trabajados en clase, fortaleciendo la creatividad y el aprendizaje práctico.',
                'tag' => 'Proyectos',
                'icon' => 'fa-lightbulb',
                'image' => 'images/novedad-proyectos-de-aula.jpg',
            ],
            [
                'date' => '18 de septiembre de 2026',
                'title' => 'Jornada cívica escolar',
                'excerpt' => 'Estudiantes de secundaria se reúnen en el patio para participar en actividades de convivencia y vida escolar.',
                'tag' => 'Convivencia',
                'icon' => 'fa-calendar-alt',
                'image' => 'images/novedad-jornada-escolar.jpg',
            ],
            [
                'date' => '8 de octubre de 2026',
                'title' => 'Encuentro con las familias',
                'excerpt' => 'Docentes, familias y estudiantes comparten un encuentro en el aula para acompañar la trayectoria escolar.',
                'tag' => 'Familias',
                'icon' => 'fa-users',
                'image' => 'images/novedad-encuentro-familias.jpg',
            ],
            [
                'date' => '8 de octubre de 2026',
                'title' => 'Formación para la comunidad educativa',
                'excerpt' => 'La comunidad educativa participa en una jornada formativa dentro de nuestra institución educativa secundaria.',
                'tag' => 'Formación',
                'icon' => 'fa-graduation-cap',
                'image' => 'images/novedad-formacion-escolar.jpg',
            ],
            [
                'date' => '14 de octubre de 2026',
                'title' => 'Nuestra comunidad estudiantil',
                'excerpt' => 'Estudiantes de secundaria comparten una jornada de encuentro en el patio de su institución.',
                'tag' => 'Estudiantes',
                'icon' => 'fa-users',
                'image' => 'images/novedad-estudiantes-secundaria.jpg',
            ],
            [
                'date' => '22 de octubre de 2026',
                'title' => 'Salida pedagógica de secundaria',
                'excerpt' => 'Estudiantes exploran su entorno y realizan aprendizajes fuera del aula junto a su comunidad escolar.',
                'tag' => 'Experiencias',
                'icon' => 'fa-compass',
                'image' => 'images/novedad-salida-pedagogica.jpg',
            ],
            [
                'date' => '23 de octubre de 2026',
                'title' => 'Aprendizaje en el aula',
                'excerpt' => 'Estudiantes y docentes participan en actividades formativas y de aprendizaje en el aula.',
                'tag' => 'Aprendizaje',
                'icon' => 'fa-book-open',
                'image' => 'images/novedad-aprendizaje-aula.jpg',
            ],
            [
                'date' => '23 de octubre de 2026',
                'title' => 'Ciencias en secundaria',
                'excerpt' => 'Los estudiantes desarrollan actividades de observación y experimentación como parte de su aprendizaje en ciencias.',
                'tag' => 'Ciencias',
                'icon' => 'fa-lightbulb',
                'image' => 'images/novedad-ciencias-secundaria.jpg',
            ],
            [
                'date' => '23 de octubre de 2026',
                'title' => 'Exposiciones y trabajos de estudiantes',
                'excerpt' => 'Estudiantes presentan sus trabajos y comparten materiales elaborados durante sus actividades de aprendizaje.',
                'tag' => 'Proyectos',
                'icon' => 'fa-graduation-cap',
                'image' => 'images/novedad-trabajos-estudiantiles.jpg',
            ],
            [
                'date' => '26 de octubre de 2026',
                'title' => 'Comunidad educativa en nuestra institución',
                'excerpt' => 'Estudiantes y docentes se reúnen para compartir actividades de la vida escolar secundaria.',
                'tag' => 'Comunidad',
                'icon' => 'fa-users',
                'image' => 'images/novedad-comunidad-educativa.jpg',
            ],
        ];
        $updates = [
            SiteSectionSettings::settingKey('novedades', 'description') => 'Conoce las actividades, clases y experiencias de aprendizaje de nuestra institución educativa secundaria en Tumbadén.',
            SiteSectionSettings::settingKey('novedades', 'news_count') => count($news),
        ];

        foreach ($news as $index => $item) {
            $number = $index + 1;
            foreach ($item as $field => $value) {
                if ($field === 'image') {
                    $updates[SiteSectionSettings::settingKey('novedades', 'news_'.$number.'_image')] = $value;
                } else {
                    $updates[SiteSectionSettings::settingKey('novedades', 'news_'.$number.'_'.$field)] = $value;
                }
            }
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
