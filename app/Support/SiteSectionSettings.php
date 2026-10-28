<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SiteSectionSettings
{
    public static function definitions(): array
    {
        $newsDefaults = [
            ['10 de junio de 2026', 'Jornada cívica escolar', 'Estudiantes, docentes y familias participan en actividades formativas y de convivencia de nuestra institución secundaria.', 'Comunidad'],
            ['5 de junio de 2026', 'Proyectos de estudiantes', 'Los estudiantes de secundaria presentan sus proyectos y comparten lo aprendido en clase.', 'Académico'],
            ['28 de mayo de 2026', 'Formación para nuestra comunidad', 'Docentes y familias fortalecen el acompañamiento y la educación de los adolescentes.', 'Formación'],
            ['12 de mayo de 2026', 'Encuentro con las familias', 'La comunicación entre familias y docentes acompaña el desarrollo de los estudiantes de secundaria.', 'Familias'],
            ['2 de mayo de 2026', 'Lectura y aprendizaje práctico', 'La lectura, la exploración científica y las actividades de aula enriquecen la formación secundaria.', 'Aprendizaje'],
            ['20 de abril de 2026', 'Aprendizaje fuera del aula', 'Una salida pedagógica permite a los estudiantes de secundaria conocer y explorar su entorno.', 'Experiencias'],
        ];
        $newsImages = [
            'images/comunidad-educativa.jpg',
            'images/exposicion-estudiantil.jpg',
            'images/formacion-docente.jpg',
            'images/encuentro-con-familias.jpg',
            'images/aprendizaje-en-el-aula.jpg',
            'images/salida-pedagogica.jpg',
        ];
        $newsIcons = ['fa-calendar-alt', 'fa-graduation-cap', 'fa-lightbulb', 'fa-users', 'fa-book-open', 'fa-compass'];
        $newsFields = [];
        $newsMedia = [];
        $newsFields['news_count'] = ['label' => 'Cantidad de novedades', 'default' => count($newsDefaults), 'max' => 30];
        for ($number = 1; $number <= 30; $number++) {
            $defaults = $newsDefaults[$number - 1] ?? ['', '', '', ''];
            [$date, $title, $excerpt, $tag] = $defaults;
            $newsFields['news_'.$number.'_date'] = ['label' => 'Novedad '.$number.' · fecha', 'default' => $date, 'max' => 80];
            $newsFields['news_'.$number.'_title'] = ['label' => 'Novedad '.$number.' · título', 'default' => $title, 'max' => 200];
            $newsFields['news_'.$number.'_excerpt'] = ['label' => 'Novedad '.$number.' · descripción', 'type' => 'textarea', 'default' => $excerpt, 'max' => 1000];
            $newsFields['news_'.$number.'_tag'] = ['label' => 'Novedad '.$number.' · categoría', 'default' => $tag, 'max' => 80];
            $newsFields['news_'.$number.'_icon'] = ['label' => 'Novedad '.$number.' · icono', 'type' => 'icon', 'default' => $newsIcons[($number - 1) % count($newsIcons)], 'max' => 50];
            $newsFields['news_'.$number.'_starts_at'] = ['label' => 'Novedad '.$number.' · mostrar desde', 'type' => 'date', 'default' => '', 'max' => 10];
            $newsFields['news_'.$number.'_ends_at'] = ['label' => 'Novedad '.$number.' · mostrar hasta', 'type' => 'date', 'default' => '', 'max' => 10];
            $newsMedia['news_'.$number.'_image'] = [
                'label' => 'Novedad '.$number.' · imagen',
                'default' => $newsImages[($number - 1) % count($newsImages)],
            ];
        }

        $footerFields = [
            'institution_name' => ['label' => 'Nombre institucional', 'default' => config('school.name'), 'max' => 200],
            'short_name' => ['label' => 'Nombre corto de la institución', 'default' => config('school.short_name'), 'max' => 100],
            'year' => ['label' => 'Año del copyright', 'default' => config('school.year'), 'max' => 4],
            'motto' => ['label' => 'Lema institucional', 'default' => config('school.motto'), 'max' => 300],
            'director_label' => ['label' => 'Etiqueta de dirección', 'default' => 'Director', 'max' => 80],
            'director' => ['label' => 'Nombre del director', 'default' => config('school.director'), 'max' => 200],
            'navigation_title' => ['label' => 'Título de navegación', 'default' => 'Navegación', 'max' => 100],
            'contact_title' => ['label' => 'Título de contacto', 'default' => 'Contacto', 'max' => 100],
            'address' => ['label' => 'Dirección', 'default' => config('school.address'), 'max' => 300],
            'phone' => ['label' => 'Teléfono', 'default' => config('school.phone'), 'max' => 80],
            'email' => ['label' => 'Correo electrónico', 'default' => config('school.email'), 'max' => 200],
            'social_facebook' => ['label' => 'Enlace Facebook', 'default' => '', 'max' => 2048],
            'social_instagram' => ['label' => 'Enlace Instagram', 'default' => '', 'max' => 2048],
            'social_youtube' => ['label' => 'Enlace YouTube', 'default' => '', 'max' => 2048],
            'social_twitter' => ['label' => 'Enlace Twitter/X', 'default' => '', 'max' => 2048],
            'copyright' => ['label' => 'Texto de derechos reservados', 'default' => 'Todos los derechos reservados.', 'max' => 250],
            'credit' => ['label' => 'Texto de crédito', 'default' => 'Desarrollado con ♥ para la comunidad educativa.', 'max' => 300],
        ];
        $footerNavigationDefaults = [
            ['Inicio', '#inicio'],
            ['¿Quiénes somos?', '#quienes-somos'],
            ['Publicaciones', '#publicaciones'],
            ['Proyecto IJENT', '#proyecto'],
            ['Novedades', '#novedades'],
        ];
        foreach ($footerNavigationDefaults as $index => [$label, $url]) {
            $number = $index + 1;
            $footerFields['nav_'.$number.'_label'] = ['label' => 'Enlace '.$number.' · texto', 'default' => $label, 'max' => 100];
            $footerFields['nav_'.$number.'_url'] = ['label' => 'Enlace '.$number.' · destino', 'default' => $url, 'max' => 2048];
        }
        $publicationDefaults = [
            ['Exploración del entorno local', 'Una experiencia de aprendizaje para que nuestros estudiantes de secundaria conozcan y exploren su entorno.'],
            ['Lectura y aprendizaje en el aula', 'Las actividades de lectura, ciencia y exploración fortalecen los aprendizajes de los estudiantes de secundaria.'],
            ['Participación estudiantil', 'Estudiantes de secundaria y docentes comparten sus proyectos y experiencias formativas.'],
        ];
        $publicationFields = [
            'publication_count' => ['label' => 'Cantidad de publicaciones', 'default' => count($publicationDefaults), 'max' => 30],
        ];
        $publicationMedia = [];
        $publicationImages = [
            'images/salida-pedagogica.jpg',
            'images/aprendizaje-en-el-aula.jpg',
            'images/equipo-estudiantil.jpg',
        ];
        $publicationIcons = ['fa-compass', 'fa-book-open', 'fa-users'];
        for ($number = 1; $number <= 30; $number++) {
            [$title, $description] = $publicationDefaults[$number - 1] ?? ['', ''];
            $publicationFields['slide_'.$number.'_title'] = ['label' => 'Publicación '.$number.' · título', 'default' => $title, 'max' => 200];
            $publicationFields['slide_'.$number.'_description'] = ['label' => 'Publicación '.$number.' · descripción', 'type' => 'textarea', 'default' => $description, 'max' => 1000];
            $publicationFields['slide_'.$number.'_icon'] = ['label' => 'Publicación '.$number.' · icono', 'type' => 'icon', 'default' => $publicationIcons[$number - 1] ?? 'fa-book-open', 'max' => 50];
            $publicationFields['slide_'.$number.'_starts_at'] = ['label' => 'Publicación '.$number.' · mostrar desde', 'default' => '', 'max' => 10];
            $publicationFields['slide_'.$number.'_ends_at'] = ['label' => 'Publicación '.$number.' · mostrar hasta', 'default' => '', 'max' => 10];
            $publicationMedia['slide_'.$number.'_image'] = [
                'label' => 'Publicación '.$number.' · imagen',
                'default' => $publicationImages[($number - 1) % count($publicationImages)],
            ];
        }

        return [
            'quienes-somos' => [
                'label' => 'Quiénes somos',
                'description' => 'Historia, misión, visión, valores e imágenes. El contenido institucional publicado se conserva debajo.',
                'fields' => [
                    'eyebrow' => ['label' => 'Texto superior', 'default' => 'Educación secundaria en Tumbadén', 'max' => 100],
                    'title' => ['label' => 'Título de la sección', 'default' => '¿Quiénes somos?', 'max' => 150],
                    'intro_heading' => ['label' => 'Título de presentación', 'default' => 'Institución Educativa Secundaria "Salomón Cáceres Oliva" — Tumbadén', 'max' => 200],
                    'intro_first' => ['label' => 'Primer párrafo', 'type' => 'textarea', 'default' => 'Somos una institución educativa pública de nivel secundario ubicada en Tumbadén, distrito de San Pablo, región Cajamarca — Perú. Acompañamos a adolescentes en su formación académica y personal, promoviendo el pensamiento crítico, la autonomía y el compromiso con su comunidad.', 'max' => 1000],
                    'intro_second' => ['label' => 'Segundo párrafo', 'type' => 'textarea', 'default' => 'Nuestra comunidad reúne a estudiantes, docentes y familias para construir aprendizajes sólidos, fortalecer la convivencia y preparar a los jóvenes para continuar sus estudios y participar activamente en la sociedad.', 'max' => 1000],
                    'motto' => ['label' => 'Lema destacado', 'default' => config('school.motto'), 'max' => 300],
                    'motto_caption' => ['label' => 'Texto bajo el lema', 'default' => 'Valores que guían nuestra formación secundaria', 'max' => 150],
                    'feature_academic' => ['label' => 'Valor destacado 1', 'default' => 'Aprendizaje y pensamiento crítico', 'max' => 100],
                    'feature_academic_icon' => ['label' => 'Icono · Valor destacado 1', 'type' => 'icon', 'default' => 'fa-star', 'max' => 50],
                    'feature_values' => ['label' => 'Valor destacado 2', 'default' => 'Formación en valores', 'max' => 100],
                    'feature_values_icon' => ['label' => 'Icono · Valor destacado 2', 'type' => 'icon', 'default' => 'fa-heart', 'max' => 50],
                    'feature_innovation' => ['label' => 'Valor destacado 3', 'default' => 'Proyectos y aprendizaje aplicado', 'max' => 100],
                    'feature_innovation_icon' => ['label' => 'Icono · Valor destacado 3', 'type' => 'icon', 'default' => 'fa-lightbulb', 'max' => 50],
                    'feature_community' => ['label' => 'Valor destacado 4', 'default' => 'Comunidad escolar secundaria', 'max' => 100],
                    'feature_community_icon' => ['label' => 'Icono · Valor destacado 4', 'type' => 'icon', 'default' => 'fa-users', 'max' => 50],
                    'inline_caption' => ['label' => 'Pie de foto 1', 'default' => 'Familias y docentes se reúnen para acompañar la formación de los estudiantes.', 'max' => 250],
                    'main_caption' => ['label' => 'Pie de foto 2', 'default' => 'Estudiantes, docentes y familias de nuestra institución secundaria.', 'max' => 250],
                    'mission_heading' => ['label' => 'Título de misión', 'default' => 'Misión', 'max' => 100],
                    'mission_icon' => ['label' => 'Icono · Misión', 'type' => 'icon', 'default' => 'fa-book-open', 'max' => 50],
                    'mission' => ['label' => 'Misión', 'type' => 'textarea', 'default' => 'Brindar educación secundaria integral e inclusiva que fortalezca los aprendizajes, los valores y las competencias de los adolescentes, en colaboración con sus familias y la comunidad de Tumbadén.', 'max' => 1000],
                    'vision_heading' => ['label' => 'Título de visión', 'default' => 'Visión', 'max' => 100],
                    'vision_icon' => ['label' => 'Icono · Visión', 'type' => 'icon', 'default' => 'fa-eye', 'max' => 50],
                    'vision' => ['label' => 'Visión', 'type' => 'textarea', 'default' => 'Ser una institución secundaria rural reconocida en la provincia de San Pablo por los aprendizajes de sus estudiantes, la convivencia respetuosa y el compromiso con su comunidad.', 'max' => 1000],
                    'values_heading' => ['label' => 'Título de valores', 'default' => 'Valores', 'max' => 100],
                    'values_icon' => ['label' => 'Icono · Valores', 'type' => 'icon', 'default' => 'fa-gem', 'max' => 50],
                    'values' => ['label' => 'Valores', 'type' => 'textarea', 'default' => 'Promovemos el respeto, la responsabilidad, la honestidad, el esfuerzo y la colaboración entre estudiantes, docentes y familias.', 'max' => 1000],
                ],
                'media' => [
                    'inline_image' => ['label' => 'Fotografía de actividades', 'default' => 'images/encuentro-con-familias.jpg'],
                    'main_image' => ['label' => 'Fotografía principal', 'default' => 'images/comunidad-educativa.jpg'],
                ],
            ],
            'proyecto' => [
                'label' => 'Proyecto IJENT',
                'description' => 'Presentación de las experiencias y recursos de aprendizaje de nuestra institución secundaria.',
                'fields' => [
                    'eyebrow' => ['label' => 'Texto superior', 'default' => 'Aprendizajes de secundaria', 'max' => 100],
                    'title' => ['label' => 'Título de la sección', 'default' => 'Experiencias de nuestra secundaria', 'max' => 150],
                    'description' => ['label' => 'Descripción de la sección', 'type' => 'textarea', 'default' => 'Conoce las actividades, proyectos y aprendizajes que forman parte de la vida diaria de estudiantes y docentes de educación secundaria en Tumbadén.', 'max' => 1000],
                    'root_label' => ['label' => 'Nombre en la ruta del explorador', 'default' => 'Experiencias de secundaria', 'max' => 100],
                    'highlight_tag' => ['label' => 'Etiqueta de la tarjeta', 'default' => 'Vista general', 'max' => 100],
                    'highlight_tag_icon' => ['label' => 'Icono · Etiqueta de la tarjeta', 'type' => 'icon', 'default' => 'fa-compass', 'max' => 50],
                    'highlight_title' => ['label' => 'Título de la tarjeta', 'default' => 'Aprendizajes que nacen en nuestras aulas', 'max' => 200],
                    'highlight_description' => ['label' => 'Descripción de la tarjeta', 'type' => 'textarea', 'default' => 'Explora el trabajo de los estudiantes de secundaria, sus proyectos de aula y las experiencias que comparten con docentes y familias.', 'max' => 1000],
                    'pill_1' => ['label' => 'Recurso destacado 1', 'default' => 'Proyectos de estudiantes', 'max' => 100],
                    'pill_1_icon' => ['label' => 'Icono · Recurso destacado 1', 'type' => 'icon', 'default' => 'fa-folder-open', 'max' => 50],
                    'pill_2' => ['label' => 'Recurso destacado 2', 'default' => 'Aprendizajes por grado', 'max' => 100],
                    'pill_2_icon' => ['label' => 'Icono · Recurso destacado 2', 'type' => 'icon', 'default' => 'fa-layer-group', 'max' => 50],
                    'pill_3' => ['label' => 'Recurso destacado 3', 'default' => 'Materiales educativos', 'max' => 100],
                    'pill_3_icon' => ['label' => 'Icono · Recurso destacado 3', 'type' => 'icon', 'default' => 'fa-file-alt', 'max' => 50],
                    'explore_hint' => ['label' => 'Indicación de navegación', 'default' => 'Recorre las actividades y materiales educativos organizados para nuestra comunidad secundaria.', 'max' => 250],
                    'drive_loading_text' => ['label' => 'Texto mientras cargan los recursos', 'default' => 'Cargando experiencias y materiales de secundaria…', 'max' => 200],
                    'drive_fallback_title' => ['label' => 'Título de vista previa de respaldo', 'default' => 'Aprendizaje fuera del aula', 'max' => 200],
                    'drive_fallback_description' => ['label' => 'Descripción de vista previa de respaldo', 'type' => 'textarea', 'default' => 'Estudiantes y docentes exploran el entorno como parte de sus actividades de aprendizaje de secundaria.', 'max' => 1000],
                    'drive_open_label' => ['label' => 'Texto del enlace a Drive', 'default' => 'Abrir en Drive', 'max' => 100],
                ],
                'media' => [
                    'image' => ['label' => 'Imagen destacada', 'default' => 'images/salida-pedagogica.jpg'],
                ],
            ],
            'novedades' => [
                'label' => 'Novedades',
                'description' => 'Edita el encabezado, administra hasta 30 novedades y programa el periodo en que cada una aparece.',
                'fields' => array_merge([
                    'eyebrow' => ['label' => 'Texto superior', 'default' => 'Actualidad', 'max' => 100],
                    'title' => ['label' => 'Título de la sección', 'default' => 'Novedades', 'max' => 150],
                    'description' => ['label' => 'Descripción', 'type' => 'textarea', 'default' => 'Actividades, proyectos y encuentros que muestran el aprendizaje y la vida cotidiana de nuestra institución secundaria.', 'max' => 1000],
                ], $newsFields),
                'media' => $newsMedia,
            ],
            'publicaciones' => [
                'label' => 'Publicaciones',
                'description' => 'Administra hasta 30 publicaciones con su imagen y periodo de visibilidad.',
                'fields' => array_merge([
                    'eyebrow' => ['label' => 'Texto superior', 'default' => 'Recursos', 'max' => 100],
                    'title' => ['label' => 'Título de la sección', 'default' => 'Nuestras publicaciones', 'max' => 150],
                ], $publicationFields),
                'media' => $publicationMedia,
            ],
            'footer' => [
                'label' => 'Pie de página',
                'description' => 'Edita identidad, navegación, contacto, redes sociales y crédito del footer global.',
                'fields' => $footerFields,
                'media' => [
                    'logo' => ['label' => 'Logotipo del pie de página', 'default' => 'images/LOGO.png'],
                ],
            ],
        ];
    }

    public static function values(?string $section = null): array
    {
        $definitions = self::definitions();
        if ($section !== null && ! isset($definitions[$section])) {
            return [];
        }

        $sections = $section === null ? $definitions : [$section => $definitions[$section]];
        $settingKeys = [];
        foreach ($sections as $key => $definition) {
            foreach (array_keys($definition['fields']) as $field) {
                $settingKeys[] = self::settingKey($key, $field);
                if ($key === 'footer') {
                    $legacyKey = match ($field) {
                        'institution_name' => 'site_name',
                        'short_name' => 'site_short_name',
                        'motto' => 'site_motto',
                        'year' => 'site_year',
                        'director' => 'site_director',
                        default => str_starts_with($field, 'social_') ? $field : null,
                    };
                    if ($legacyKey) {
                        $settingKeys[] = $legacyKey;
                    }
                }
            }
            foreach (array_keys($definition['media']) as $field) {
                $settingKeys[] = self::settingKey($key, $field);
            }
        }
        $storedValues = Schema::hasTable('settings')
            ? Setting::query()->whereIn('key', array_unique($settingKeys))->pluck('value', 'key')->all()
            : [];
        $values = [];
        foreach ($sections as $key => $definition) {
            $values[$key] = [];
            foreach ($definition['fields'] as $field => $config) {
                $settingKey = self::settingKey($key, $field);
                $legacyKey = $key === 'footer' ? match ($field) {
                    'institution_name' => 'site_name',
                    'short_name' => 'site_short_name',
                    'motto' => 'site_motto',
                    'year' => 'site_year',
                    'director' => 'site_director',
                    default => str_starts_with($field, 'social_') ? $field : null,
                } : null;
                $value = $storedValues[$settingKey]
                    ?? ($legacyKey ? ($storedValues[$legacyKey] ?? null) : null)
                    ?? $config['default'];
                if (($config['type'] ?? null) === 'icon'
                    && ! array_key_exists((string) $value, self::iconChoices())) {
                    $value = $config['default'];
                }
                if ($key === 'footer' && str_starts_with($field, 'social_') && ! preg_match('#^https://[^\s]+$#i', (string) $value)) {
                    $value = '';
                }
                $values[$key][$field] = $value;
            }
            foreach ($definition['media'] as $field => $config) {
                $settingKey = self::settingKey($key, $field);
                $stored = $storedValues[$settingKey] ?? null;
                $mediaPath = $stored ?: $config['default'];
                $values[$key]['media'][$field] = preg_match('#^(?:[a-z][a-z0-9+.-]*:|/)#i', $mediaPath)
                    ? $mediaPath
                    : asset($mediaPath);
                $values[$key]['stored_media'][$field] = $stored;
            }
        }

        return $section === null ? $values : $values[$section];
    }

    public static function settingKey(string $section, string $field): string
    {
        return 'site_section_'.$section.'_'.$field;
    }

    public static function publicationIcons(): array
    {
        return self::iconChoices();
    }

    public static function iconChoices(): array
    {
        return [
            'fa-star' => 'Destacados',
            'fa-heart' => 'Valores',
            'fa-lightbulb' => 'Ideas',
            'fa-users' => 'Comunidad',
            'fa-book-open' => 'Lectura',
            'fa-eye' => 'Visión',
            'fa-gem' => 'Valores institucionales',
            'fa-compass' => 'Exploración',
            'fa-folder-open' => 'Carpetas',
            'fa-layer-group' => 'Secciones',
            'fa-file-alt' => 'Documentos',
            'fa-newspaper' => 'Noticias',
            'fa-seedling' => 'Naturaleza',
            'fa-flask' => 'Ciencia',
            'fa-running' => 'Deportes',
            'fa-graduation-cap' => 'Educación',
            'fa-calendar-alt' => 'Eventos',
            'fa-trophy' => 'Logros',
            'fa-palette' => 'Arte',
            'fa-microscope' => 'Investigación',
            'fa-bullhorn' => 'Comunicados',
        ];
    }
}
