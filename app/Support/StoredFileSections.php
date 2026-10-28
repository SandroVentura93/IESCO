<?php

namespace App\Support;

use App\Models\StoredFile;
use App\Models\Course;
use Illuminate\Support\Str;

class StoredFileSections
{
    public static function destinations(): array
    {
        return [
            'Proyecto_IJENT' => [
                'label' => 'Proyecto IJENT · Recursos institucionales',
                'directory' => 'proyecto-ijent',
                'private' => false,
                'project' => true,
            ],
            'Plan_Estrategico_IJENT' => [
                'label' => 'Proyecto IJENT · Plan Estratégico IJENT',
                'directory' => 'plan-estrategico-ijent',
                'private' => false,
                'project' => true,
            ],
            'Recursos_Pedagogicos' => [
                'label' => 'Proyecto IJENT · Recursos pedagógicos',
                'directory' => 'recursos-pedagogicos',
                'private' => false,
                'project' => true,
            ],
            'Actividades_Extracurriculares' => [
                'label' => 'Proyecto IJENT · Actividades extracurriculares',
                'directory' => 'actividades-extracurriculares',
                'private' => false,
                'project' => true,
            ],
            'Evaluaciones_Seguimiento' => [
                'label' => 'Proyecto IJENT · Evaluaciones y seguimiento',
                'directory' => 'evaluaciones-seguimiento',
                'private' => false,
                'project' => true,
            ],
            'Formacion_Docente_2026' => [
                'label' => 'Proyecto IJENT · Formación docente 2026',
                'directory' => 'formacion-docente-2026',
                'private' => false,
                'project' => true,
            ],
            'Novedades' => [
                'label' => 'Novedades',
                'directory' => 'novedades',
                'private' => false,
                'project' => false,
            ],
            'Publicaciones' => [
                'label' => 'Publicaciones',
                'directory' => 'publicaciones',
                'private' => false,
                'project' => false,
            ],
            StoredFile::MEDIA_LIBRARY_SECTION => [
                'label' => 'Biblioteca multimedia (privada)',
                'directory' => 'biblioteca-multimedia',
                'private' => true,
                'project' => false,
            ],
        ];
    }

    public static function publicOptions(): array
    {
        return array_filter(
            self::destinations(),
            static fn (array $destination): bool => !$destination['private']
        );
    }

    public static function projectOptions(): array
    {
        return array_filter(
            self::publicOptions(),
            static fn (array $destination): bool => $destination['project']
        );
    }

    public static function submenuOptions(): array
    {
        $destinations = self::destinations();
        $submenuSections = [
            'Actividades_Extracurriculares' => 'Actividades Extracurriculares',
            'Plan_Estrategico_IJENT' => 'Plan Estratégico IJENT',
            'Recursos_Pedagogicos' => 'Recursos Pedagógicos',
        ];

        $options = [];
        foreach ($submenuSections as $section => $label) {
            $options[$section] = $destinations[$section];
            $options[$section]['label'] = $label;
        }

        return $options;
    }

    public static function subfolders(string $section): array
    {
        if (!self::hasCourseHierarchy($section)) {
            return [];
        }

        return [
            '1° Secundaria' => [
                'label' => '1° Secundaria',
                'directory' => '1-secundaria',
            ],
            '2° Secundaria' => [
                'label' => '2° Secundaria',
                'directory' => '2-secundaria',
            ],
            '3° Secundaria' => [
                'label' => '3° Secundaria',
                'directory' => '3-secundaria',
            ],
            '4° Secundaria' => [
                'label' => '4° Secundaria',
                'directory' => '4-secundaria',
            ],
            '5° Secundaria' => [
                'label' => '5° Secundaria',
                'directory' => '5-secundaria',
            ],
        ];
    }

    public static function courseHierarchySections(): array
    {
        return ['Actividades_Extracurriculares', 'Recursos_Pedagogicos'];
    }

    public static function hasCourseHierarchy(string $section): bool
    {
        return in_array($section, self::courseHierarchySections(), true);
    }

    public static function courses(): array
    {
        return Course::query()->orderBy('name')->get(['id', 'name'])
            ->map(static fn (Course $course): array => [
                'id' => $course->id,
                'name' => $course->name,
            ])
            ->all();
    }

    public static function courseDirectory(int|string $courseId): string
    {
        abort_if(!ctype_digit((string) $courseId), 422, 'El curso seleccionado no es válido.');

        return 'curso-'.$courseId;
    }

    public static function fileTypeSubfolders(): array
    {
        return [
            'image' => [
                'label' => 'Imagen',
                'directory' => 'imagen',
            ],
            'document' => [
                'label' => 'Documento',
                'directory' => 'documento',
            ],
            'video' => [
                'label' => 'Video',
                'directory' => 'video',
            ],
            'audio' => [
                'label' => 'Audio',
                'directory' => 'audio',
            ],
            'other' => [
                'label' => 'Otro',
                'directory' => 'otro',
            ],
        ];
    }

    public static function labels(array $destinations): array
    {
        return array_map(
            static fn (array $destination): string => $destination['label'],
            $destinations
        );
    }

    public static function directory(
        string $section,
        ?string $subfolder = null,
        ?string $fileType = null,
        int|string|null $courseId = null
    ): string
    {
        $destination = self::destinations()[$section] ?? null;
        abort_if($destination === null, 422, 'La carpeta seleccionada no es válida.');

        $directory = 'files/'.$destination['directory'];
        if ($subfolder !== null && $subfolder !== '') {
            $child = self::subfolders($section)[$subfolder] ?? null;
            abort_if($child === null, 422, 'La subcarpeta seleccionada no es válida.');
            $directory .= '/'.$child['directory'];
        }

        if ($courseId !== null && $courseId !== '') {
            abort_if(
                !self::hasCourseHierarchy($section) || $subfolder === null || $subfolder === '',
                422,
                'El curso requiere una carpeta de grado.'
            );
            abort_unless(Course::query()->whereKey($courseId)->exists(), 422, 'El curso seleccionado no es válido.');
            $directory .= '/'.self::courseDirectory($courseId);
        }

        if ($fileType !== null && $fileType !== '') {
            abort_if(
                !self::hasCourseHierarchy($section) || $subfolder === null || $subfolder === '',
                422,
                'La categoría requiere una carpeta de grado.'
            );
            $category = self::fileTypeSubfolders()[$fileType] ?? null;
            abort_if($category === null, 422, 'La categoría de archivo seleccionada no es válida.');
            $directory .= '/'.$category['directory'];
        }

        return $directory;
    }

    public static function subfolderFromPath(string $section, string $path): ?string
    {
        $baseDirectory = self::directory($section);
        if (!str_starts_with($path, $baseDirectory.'/')) {
            return null;
        }

        $directory = explode('/', substr($path, strlen($baseDirectory) + 1))[0];
        foreach (self::subfolders($section) as $key => $subfolder) {
            if ($subfolder['directory'] === $directory) {
                return $key;
            }
        }

        return null;
    }

    public static function isPrivate(string $section): bool
    {
        $destination = self::destinations()[$section] ?? null;

        return $destination !== null && $destination['private'];
    }
}
