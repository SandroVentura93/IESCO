<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use App\Support\HtmlContentSanitizer;
use App\Support\SiteSectionSettings;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PageController extends Controller
{
    public function __construct(private readonly HtmlContentSanitizer $sanitizer) {}

    public function index(Request $request)
    {
        $sections = Page::sections();
        $filters = $request->validate([
            'section' => 'nullable|string|in:'.implode(',', array_keys($sections)),
            'q' => 'nullable|string|max:120',
        ]);

        $query = Page::query()
            ->orderBy('section')
            ->orderBy('position')
            ->orderBy('title');

        if (! empty($filters['section'])) {
            $query->where('section', $filters['section']);
        }

        if (! empty($filters['q'])) {
            $query->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%'.$filters['q'].'%')
                    ->orWhere('slug', 'like', '%'.$filters['q'].'%');
            });
        }

        return view('admin.pages.index', [
            'pages' => $query->paginate(15)->withQueryString(),
            'sections' => $sections,
            'selectedSection' => $filters['section'] ?? '',
            'search' => $filters['q'] ?? '',
            'totalPages' => Page::count(),
            'publishedPages' => Page::where('published', true)->count(),
            'homepage' => Page::where('slug', 'inicio')->first(),
            'sectionEditors' => SiteSectionSettings::definitions(),
        ]);
    }

    public function editSection(string $section)
    {
        $definitions = SiteSectionSettings::definitions();
        abort_unless(isset($definitions[$section]), 404);

        return view('admin.pages.sections.edit', [
            'section' => $section,
            'definition' => $definitions[$section],
            'values' => SiteSectionSettings::values($section),
            'iconChoices' => SiteSectionSettings::iconChoices(),
        ]);
    }

    public function updateSection(Request $request, string $section)
    {
        $definitions = SiteSectionSettings::definitions();
        abort_unless(isset($definitions[$section]), 404);

        $rules = [];
        foreach ($definitions[$section]['fields'] as $field => $config) {
            if ($section === 'novedades' && ($field === 'news_count' || preg_match('/^news_\d+_/', $field))) {
                continue;
            }
            if ($section === 'publicaciones' && ($field === 'publication_count' || preg_match('/^slide_\d+_/', $field))) {
                continue;
            }
            $rules[$field] = $section === 'footer'
                ? 'present|nullable|string|max:'.$config['max']
                : 'present|string|max:'.$config['max'];
            if (($config['type'] ?? null) === 'icon') {
                $rules[$field] = 'required|string|in:'.implode(',', array_keys(SiteSectionSettings::iconChoices()));
            } elseif ($section === 'footer' && $field === 'email') {
                $rules[$field] = 'present|nullable|email|max:200';
            } elseif ($section === 'footer' && str_starts_with($field, 'social_')) {
                $rules[$field] = ['present', 'nullable', 'url', 'max:2048', 'regex:/^https:\/\/[^\s]+$/i'];
            } elseif ($section === 'footer' && str_starts_with($field, 'nav_') && str_ends_with($field, '_url')) {
                $rules[$field] = ['present', 'nullable', 'string', 'max:2048', 'regex:/^(|#[-a-zA-Z0-9_]+|\/(?!\/)[a-zA-Z0-9_\-\/?#=&.%]*|https:\/\/[^\s]+)$/i'];
            }
        }
        $newsCount = 0;
        $deletedNews = [];
        $publicationCount = 0;
        $deletedPublications = [];
        if ($section === 'novedades') {
            $rules['news_count'] = 'required|integer|min:0|max:30';
            $validatedCount = $request->validate(['news_count' => $rules['news_count']]);
            $newsCount = (int) $validatedCount['news_count'];
            $deletedData = $request->validate([
                'news_deleted' => 'nullable|array',
                'news_deleted.*' => 'integer|min:1|max:30',
            ]);
            $deletedNews = array_map('intval', $deletedData['news_deleted'] ?? []);

            for ($number = 1; $number <= $newsCount; $number++) {
                if (in_array($number, $deletedNews, true)) {
                    continue;
                }
                $rules['news_'.$number.'_date'] = 'present|nullable|string|max:80';
                $rules['news_'.$number.'_title'] = 'required|string|max:200';
                $rules['news_'.$number.'_excerpt'] = 'present|nullable|string|max:1000';
                $rules['news_'.$number.'_tag'] = 'present|nullable|string|max:80';
                $rules['news_'.$number.'_icon'] = 'required|string|in:'.implode(',', array_keys(SiteSectionSettings::iconChoices()));
                $rules['news_'.$number.'_starts_at'] = 'present|nullable|date_format:Y-m-d';
                $rules['news_'.$number.'_ends_at'] = 'present|nullable|date_format:Y-m-d';
            }
        }
        if ($section === 'publicaciones') {
            $rules['publication_count'] = 'required|integer|min:0|max:30';
            $validatedCount = $request->validate(['publication_count' => $rules['publication_count']]);
            $publicationCount = (int) $validatedCount['publication_count'];
            $deletedData = $request->validate([
                'publication_deleted' => 'nullable|array',
                'publication_deleted.*' => 'integer|min:1|max:30',
            ]);
            $deletedPublications = array_map('intval', $deletedData['publication_deleted'] ?? []);

            for ($number = 1; $number <= $publicationCount; $number++) {
                if (in_array($number, $deletedPublications, true)) {
                    continue;
                }
                $rules['slide_'.$number.'_title'] = 'required|string|max:200';
                $rules['slide_'.$number.'_description'] = 'present|nullable|string|max:1000';
                $rules['slide_'.$number.'_icon'] = 'required|string|in:'.implode(',', array_keys(SiteSectionSettings::iconChoices()));
                $rules['slide_'.$number.'_starts_at'] = 'present|nullable|date_format:Y-m-d';
                $rules['slide_'.$number.'_ends_at'] = 'present|nullable|date_format:Y-m-d';
            }
        }
        foreach ($definitions[$section]['media'] as $field => $config) {
            if ($section === 'novedades' && ! preg_match('/^news_(\d+)_image$/', $field, $matches)) {
                continue;
            }
            if ($section === 'novedades' && (int) $matches[1] > $newsCount) {
                continue;
            }
            if ($section === 'publicaciones' && (! preg_match('/^slide_(\d+)_image$/', $field, $matches)
                || (int) $matches[1] > $publicationCount
                || in_array((int) $matches[1], $deletedPublications, true))) {
                continue;
            }
            $rules['media_'.$field] = 'nullable|image|max:5120';
            $rules['remove_'.$field] = 'nullable|boolean';
        }
        $data = $request->validate($rules);

        if ($section === 'novedades') {
            for ($number = 1; $number <= $newsCount; $number++) {
                $start = $data['news_'.$number.'_starts_at'] ?? null;
                $end = $data['news_'.$number.'_ends_at'] ?? null;
                if (filled($start) && filled($end) && $end < $start) {
                    return back()
                        ->withErrors(['news_'.$number.'_ends_at' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.'])
                        ->withInput();
                }
            }
        }
        if ($section === 'publicaciones') {
            for ($number = 1; $number <= $publicationCount; $number++) {
                if (in_array($number, $deletedPublications, true)) {
                    continue;
                }
                $start = $data['slide_'.$number.'_starts_at'] ?? null;
                $end = $data['slide_'.$number.'_ends_at'] ?? null;
                if (filled($start) && filled($end) && $end < $start) {
                    return back()
                        ->withErrors(['slide_'.$number.'_ends_at' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.'])
                        ->withInput();
                }
            }
        }

        foreach ($definitions[$section]['fields'] as $field => $config) {
            if ($section === 'novedades' && preg_match('/^news_(\d+)_/', $field, $matches) && (int) $matches[1] > $newsCount) {
                continue;
            }
            if ($section === 'publicaciones' && preg_match('/^slide_(\d+)_/', $field, $matches)
                && ((int) $matches[1] > $publicationCount || in_array((int) $matches[1], $deletedPublications, true))) {
                continue;
            }
            Setting::set(SiteSectionSettings::settingKey($section, $field), $data[$field] ?? '');
        }

        foreach ($definitions[$section]['media'] as $field => $config) {
            if ($section === 'novedades' && ! preg_match('/^news_(\d+)_image$/', $field, $matches)) {
                continue;
            }
            if ($section === 'novedades' && (int) $matches[1] > $newsCount) {
                continue;
            }
            if ($section === 'publicaciones' && (! preg_match('/^slide_(\d+)_image$/', $field, $matches)
                || (int) $matches[1] > $publicationCount
                || in_array((int) $matches[1], $deletedPublications, true))) {
                continue;
            }
            $settingKey = SiteSectionSettings::settingKey($section, $field);
            $previous = Setting::get($settingKey);
            $fileKey = 'media_'.$field;

            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('settings/sections/'.$section, 'public');
                if (! is_string($path)) {
                    throw new RuntimeException('No se pudo guardar la imagen de la sección.');
                }
                Setting::set($settingKey, Storage::disk('public')->url($path));
                $this->deletePublicSettingFile($previous);
            } elseif ($request->boolean('remove_'.$field)) {
                $this->deletePublicSettingFile($previous);
                Setting::set($settingKey, '');
            }
        }

        if ($section === 'novedades') {
            foreach ($definitions[$section]['fields'] as $field => $config) {
                if (preg_match('/^news_(\d+)_/', $field, $matches)
                    && ((int) $matches[1] > $newsCount || in_array((int) $matches[1], $deletedNews, true))) {
                    Setting::set(SiteSectionSettings::settingKey($section, $field), '');
                }
            }
            foreach ($definitions[$section]['media'] as $field => $config) {
                if (preg_match('/^news_(\d+)_image$/', $field, $matches)
                    && ((int) $matches[1] > $newsCount || in_array((int) $matches[1], $deletedNews, true))) {
                    $settingKey = SiteSectionSettings::settingKey($section, $field);
                    $this->deletePublicSettingFile(Setting::get($settingKey));
                    Setting::set($settingKey, '');
                }
            }
        }
        if ($section === 'publicaciones') {
            foreach ($definitions[$section]['fields'] as $field => $config) {
                if (preg_match('/^slide_(\d+)_/', $field, $matches)
                    && ((int) $matches[1] > $publicationCount || in_array((int) $matches[1], $deletedPublications, true))) {
                    Setting::set(SiteSectionSettings::settingKey($section, $field), '');
                }
            }
            foreach ($definitions[$section]['media'] as $field => $config) {
                if (preg_match('/^slide_(\d+)_image$/', $field, $matches)
                    && ((int) $matches[1] > $publicationCount || in_array((int) $matches[1], $deletedPublications, true))) {
                    $settingKey = SiteSectionSettings::settingKey($section, $field);
                    $this->deletePublicSettingFile(Setting::get($settingKey));
                    Setting::set($settingKey, '');
                }
            }
        }

        return redirect()->route('admin.pages.sections.edit', $section)
            ->with('success', 'La sección se actualizó correctamente.');
    }

    public function create(Request $request)
    {
        return view('admin.pages.create', [
            'sections' => Page::sections(),
            'homeSettings' => $this->homepageSettings(),
            'homepageMode' => $request->boolean('homepage'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedPage($request);
        $homepageSettings = $data['slug'] === 'inicio' ? $this->validatedHomepageSettings($request) : null;
        $data['content'] = $this->sanitizer->clean($data['content'] ?? null);
        $data['published'] = $request->boolean('published');

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        Page::create($data);
        if ($homepageSettings !== null) {
            $this->saveHomepageSettings($request, $homepageSettings);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Página creada y lista para publicarse.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'sections' => Page::sections(),
            'homeSettings' => $this->homepageSettings(),
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validatedPage($request, $page);
        $homepageSettings = $data['slug'] === 'inicio' ? $this->validatedHomepageSettings($request) : null;
        $data['content'] = $this->sanitizer->clean($data['content'] ?? null);
        $data['published'] = $request->boolean('published');
        $previousImage = $page->featured_image;
        $previousThumbnail = $page->featured_thumbnail;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $page->update($data);

        if ($request->hasFile('featured_image')) {
            Storage::disk('public')->delete(array_filter([$previousImage, $previousThumbnail]));
        }
        if ($homepageSettings !== null) {
            $this->saveHomepageSettings($request, $homepageSettings);
        }

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Los cambios de la página se guardaron correctamente.');
    }

    public function destroy(Page $page)
    {
        Storage::disk('public')->delete(array_filter([$page->featured_image, $page->featured_thumbnail]));
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Página eliminada.');
    }

    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->where('published', true)->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'safeContent' => $this->sanitizer->clean($page->content),
            'school' => config('school'),
        ]);
    }

    private function validatedHomepageSettings(Request $request): array
    {
        return $request->validate([
            'inicio_badge' => 'nullable|string|max:120',
            'inicio_title_prefix' => 'nullable|string|max:100',
            'inicio_title_highlight' => 'nullable|string|max:150',
            'inicio_institution_name' => 'nullable|string|max:200',
            'inicio_motto' => 'nullable|string|max:200',
            'inicio_subtitle' => 'nullable|string|max:500',
            'inicio_scroll_text' => 'nullable|string|max:60',
            'inicio_left_button_text' => 'nullable|string|max:80',
            'inicio_left_button_url' => ['nullable', 'string', 'max:2048', 'regex:/^(#[-a-zA-Z0-9_]+|\/[a-zA-Z0-9_\-\/?#=&.%]*|https:\/\/[^\s]+)$/'],
            'inicio_right_button_text' => 'nullable|string|max:80',
            'inicio_right_button_url' => ['nullable', 'string', 'max:2048', 'regex:/^(#[-a-zA-Z0-9_]+|\/[a-zA-Z0-9_\-\/?#=&.%]*|https:\/\/[^\s]+)$/'],
            'inicio_students_value' => 'nullable|integer|min:0|max:10000000',
            'inicio_students_label' => 'nullable|string|max:60',
            'inicio_teachers_value' => 'nullable|integer|min:0|max:1000000',
            'inicio_teachers_label' => 'nullable|string|max:60',
            'inicio_years_value' => 'nullable|integer|min:0|max:10000',
            'inicio_years_label' => 'nullable|string|max:60',
            'inicio_projects_value' => 'nullable|integer|min:0|max:1000000',
            'inicio_projects_label' => 'nullable|string|max:60',
            'inicio_video_url' => ['nullable', 'url', 'max:2048', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || $value === '') {
                    return;
                }

                $host = strtolower((string) parse_url($value, PHP_URL_HOST));
                $path = strtolower((string) parse_url($value, PHP_URL_PATH));
                $supportedVideoHosts = ['youtube.com', 'www.youtube.com', 'youtu.be', 'm.youtube.com', 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com'];
                $isDirectVideo = in_array(pathinfo($path, PATHINFO_EXTENSION), ['mp4', 'webm'], true);
                $isSupportedHost = in_array($host, $supportedVideoHosts, true);

                if (parse_url($value, PHP_URL_SCHEME) !== 'https' || (! $isDirectVideo && ! $isSupportedHost)) {
                    $fail('Usa un enlace HTTPS de YouTube, Vimeo o un video MP4/WEBM directo.');
                }
            }],
            'inicio_image' => 'nullable|image|max:5120',
            'inicio_logo' => 'nullable|image|max:2048',
            'inicio_video_file' => 'nullable|file|mimetypes:video/mp4,video/webm|max:51200',
            'remove_inicio_image' => 'nullable|boolean',
            'remove_inicio_logo' => 'nullable|boolean',
            'remove_inicio_video' => 'nullable|boolean',
        ]);
    }

    private function saveHomepageSettings(Request $request, array $data): void
    {

        foreach ([
            'inicio_badge',
            'inicio_title_prefix',
            'inicio_title_highlight',
            'inicio_institution_name',
            'inicio_motto',
            'inicio_subtitle',
            'inicio_scroll_text',
            'inicio_left_button_text',
            'inicio_left_button_url',
            'inicio_right_button_text',
            'inicio_right_button_url',
            'inicio_students_value',
            'inicio_students_label',
            'inicio_teachers_value',
            'inicio_teachers_label',
            'inicio_years_value',
            'inicio_years_label',
            'inicio_projects_value',
            'inicio_projects_label',
            'inicio_video_url',
        ] as $key) {
            if (array_key_exists($key, $data)) {
                Setting::set($key, $data[$key]);
            }
        }

        foreach ([
            'inicio_image' => 'inicio_image',
            'inicio_logo' => 'inicio_logo',
        ] as $field => $key) {
            if ($request->hasFile($field)) {
                $this->replacePublicSettingFile($key, $request->file($field));
            } elseif ($request->boolean('remove_'.$field)) {
                $this->removePublicSettingFile($key);
            }
        }

        if ($request->hasFile('inicio_video_file')) {
            $this->replacePublicSettingFile('inicio_video', $request->file('inicio_video_file'));
            Setting::set('inicio_video_url', '');
        } elseif (array_key_exists('inicio_video_url', $data) && $data['inicio_video_url']) {
            $this->removePublicSettingFile('inicio_video');
            Setting::set('inicio_video', '');
        } elseif ($request->boolean('remove_inicio_video')) {
            $this->removePublicSettingFile('inicio_video');
            Setting::set('inicio_video_url', '');
        }
    }

    private function replacePublicSettingFile(string $key, UploadedFile $file): void
    {
        $path = $file->store('settings/home', 'public');
        if (! is_string($path)) {
            throw new RuntimeException('No se pudo guardar el recurso de la portada.');
        }

        $previous = Setting::get($key);
        Setting::set($key, Storage::disk('public')->url($path));
        $this->deletePublicSettingFile($previous);
    }

    private function removePublicSettingFile(string $key): void
    {
        $value = Setting::get($key);
        if (! $value) {
            return;
        }

        $this->deletePublicSettingFile($value);
        Setting::set($key, '');
    }

    private function deletePublicSettingFile(?string $value): void
    {
        if (! $value) {
            return;
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (is_string($path) && str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('/storage/')));
        }
    }

    private function validatedPage(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:pages,slug'.($page ? ','.$page->id : ''),
            ],
            'section' => [
                'required',
                'string',
                'in:'.implode(',', array_keys(Page::sections())),
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $isHomepageSlug = $request->input('slug') === 'inicio';
                    if ($isHomepageSlug !== ($value === 'inicio')) {
                        $fail('La portada debe usar la sección Inicio y las demás páginas deben usar otra sección.');
                    }
                },
            ],
            'content' => 'nullable|string|max:50000',
            'featured_image' => 'nullable|image|max:5120',
            'published' => 'nullable|boolean',
            'position' => 'nullable|integer|min:0|max:9999',
        ]);
    }

    private function homepageSettings(): array
    {
        $keys = [
            'inicio_badge', 'inicio_title_prefix', 'inicio_title_highlight', 'inicio_institution_name',
            'inicio_motto', 'inicio_subtitle', 'inicio_scroll_text', 'inicio_left_button_text', 'inicio_left_button_url',
            'inicio_right_button_text', 'inicio_right_button_url', 'inicio_students_value', 'inicio_students_label',
            'inicio_teachers_value', 'inicio_teachers_label', 'inicio_years_value', 'inicio_years_label',
            'inicio_projects_value', 'inicio_projects_label', 'inicio_video_url', 'inicio_video', 'inicio_image', 'inicio_logo',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = Setting::get($key);
        }

        return $settings;
    }
}
