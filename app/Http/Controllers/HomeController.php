<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use App\Support\HtmlContentSanitizer;
use App\Support\SiteSectionSettings;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function __construct(private readonly HtmlContentSanitizer $sanitizer) {}

    public function index()
    {
        $school = config('school');
        $publishedPages = collect();

        if (Schema::hasTable('pages')) {
            $publishedPages = Page::query()
                ->where('published', true)
                ->orderBy('position')
                ->orderBy('title')
                ->get();
            $publishedPages->each(function ($page) {
                $page->content = $this->sanitizer->clean($page->content);
            });
        }

        $inicio = $publishedPages->firstWhere('slug', 'inicio');
        $quienes = $publishedPages->firstWhere('slug', 'quienes-somos');
        $sectionPages = $publishedPages
            ->reject(fn ($page) => in_array($page->slug, ['inicio', 'quienes-somos'], true))
            ->groupBy('section');
        $homeSettings = $this->homepageSettings($school);
        $siteSections = SiteSectionSettings::values();

        // If pages are not present or empty, fall back to settings-based content
        try {
            $settings = [];
            if (Schema::hasTable('settings')) {
                $keys = ['inicio_content', 'inicio_video', 'inicio_image', 'quienes_content', 'quienes_image', 'proyecto_content', 'proyecto_image', 'novedades_content', 'publicaciones_content'];
                foreach ($keys as $k) {
                    $settings[$k] = Setting::get($k);
                }
            }
            if (! $inicio && ! empty($settings['inicio_content'])) {
                $obj = new \stdClass;
                $obj->content = $this->sanitizer->clean($settings['inicio_content']);
                $obj->video = $settings['inicio_video'] ?? null;
                $obj->image = $settings['inicio_image'] ?? null;
                $inicio = $obj;
            }
            if (! $quienes && ! empty($settings['quienes_content'])) {
                $obj = new \stdClass;
                $obj->content = $this->sanitizer->clean($settings['quienes_content']);
                $obj->image = $settings['quienes_image'] ?? null;
                $quienes = $obj;
            }
            // Pass proyecto and other settings via view composer if needed
            view()->share('home_settings', $settings);
            $homeSettings['background_image'] = $homeSettings['background_image'] ?: ($settings['inicio_image'] ?? null);
            $homeSettings['video'] = $homeSettings['video'] ?: ($settings['inicio_video'] ?? null);
        } catch (\Exception $e) {
            // ignore settings loading errors
        }
        $homeSettings['video_presentation'] = $this->videoPresentation($homeSettings['video']);

        return view('home', compact('school', 'inicio', 'quienes', 'sectionPages', 'homeSettings', 'siteSections'));
    }

    private function homepageSettings(array $school): array
    {
        $defaults = [
            'badge' => 'Tumbadén, San Pablo — Cajamarca, Perú',
            'title_prefix' => 'IE.:',
            'title_highlight' => 'S.C.O.',
            'institution_name' => 'Salomón Cáceres Oliva',
            'motto' => $school['motto'],
            'subtitle' => 'Acompañamos a adolescentes en su educación secundaria, fortaleciendo sus aprendizajes, valores y proyectos de vida.',
            'scroll_text' => 'Explorar',
            'left_button_text' => 'Ver publicaciones',
            'left_button_url' => '#publicaciones',
            'right_button_text' => 'Conócenos',
            'right_button_url' => '#quienes-somos',
            'students_value' => $school['stats']['students'],
            'students_label' => 'Estudiantes',
            'teachers_value' => $school['stats']['teachers'],
            'teachers_label' => 'Docentes',
            'years_value' => $school['stats']['years'],
            'years_label' => 'Años de historia',
            'projects_value' => $school['stats']['projects'],
            'projects_label' => 'Proyectos activos',
            'background_image' => null,
            'logo' => null,
            'video' => null,
        ];

        $keys = [
            'badge' => 'inicio_badge',
            'title_prefix' => 'inicio_title_prefix',
            'title_highlight' => 'inicio_title_highlight',
            'institution_name' => 'inicio_institution_name',
            'motto' => 'inicio_motto',
            'subtitle' => 'inicio_subtitle',
            'scroll_text' => 'inicio_scroll_text',
            'left_button_text' => 'inicio_left_button_text',
            'left_button_url' => 'inicio_left_button_url',
            'right_button_text' => 'inicio_right_button_text',
            'right_button_url' => 'inicio_right_button_url',
            'students_value' => 'inicio_students_value',
            'students_label' => 'inicio_students_label',
            'teachers_value' => 'inicio_teachers_value',
            'teachers_label' => 'inicio_teachers_label',
            'years_value' => 'inicio_years_value',
            'years_label' => 'inicio_years_label',
            'projects_value' => 'inicio_projects_value',
            'projects_label' => 'inicio_projects_label',
            'background_image' => 'inicio_image',
            'logo' => 'inicio_logo',
        ];

        foreach ($keys as $field => $key) {
            $value = Setting::get($key);
            if ($value !== null && $value !== '') {
                $defaults[$field] = $field === 'students_value' || $field === 'teachers_value'
                    || $field === 'years_value' || $field === 'projects_value'
                    ? (int) $value
                    : $value;
            }
        }

        $defaults['video'] = Setting::get('inicio_video_url') ?: Setting::get('inicio_video');
        $defaults['video_url'] = Setting::get('inicio_video_url');
        if (! $defaults['video_url'] && $defaults['video']
            && ! str_starts_with((string) parse_url($defaults['video'], PHP_URL_PATH), '/storage/')
            && $this->videoPresentation($defaults['video']) !== null) {
            $defaults['video_url'] = $defaults['video'];
        }
        $defaults['video_presentation'] = $this->videoPresentation($defaults['video']);
        $defaults['customized'] = collect([
            'inicio_badge',
            'inicio_title_prefix',
            'inicio_title_highlight',
            'inicio_institution_name',
            'inicio_motto',
            'inicio_subtitle',
            'inicio_scroll_text',
        ])
            ->contains(fn ($key) => Setting::get($key) !== null && Setting::get($key) !== '');

        return $defaults;
    }

    private function videoPresentation(?string $url): ?array
    {
        if (! $url) {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (str_starts_with($path, '/storage/') && in_array($extension, ['mp4', 'webm'], true)) {
            return ['type' => 'file', 'url' => $path];
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return null;
        }

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'], true)) {
            $videoId = $host === 'youtu.be'
                ? trim($path, '/')
                : ($query['v'] ?? preg_replace('#^/(?:shorts|embed)/#', '', $path));
            if (is_string($videoId) && preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
                return ['type' => 'embed', 'url' => 'https://www.youtube-nocookie.com/embed/'.$videoId.'?autoplay=1&mute=1&loop=1&playlist='.$videoId.'&playsinline=1'];
            }
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)
            && preg_match('#(?:video/)?(\d+)#', $path, $matches)) {
            return ['type' => 'embed', 'url' => 'https://player.vimeo.com/video/'.$matches[1].'?background=1&autoplay=1&muted=1&loop=1'];
        }

        if (in_array($extension, ['mp4', 'webm'], true)) {
            return ['type' => 'file', 'url' => $url];
        }

        return null;
    }
}
