@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home-premium.css') }}?v={{ filemtime(public_path('css/home-premium.css')) }}-home-settings1" />
@endpush

@section('full_width')
@endsection

@section('content')

  {{-- ═══════════════════════════════════
       HERO
  ═══════════════════════════════════ --}}
  @php
    $legacyHeroContent = !$homeSettings['customized'] && isset($inicio) && !empty($inicio->content);
    $heroLogo = $homeSettings['logo'] ?: asset('images/LOGO.png');
  @endphp
  <section id="inicio" class="hero" aria-label="Bienvenidos">
    <div class="hero-bg" aria-hidden="true">
      @if(($homeSettings['video_presentation']['type'] ?? null) === 'file')
        <video class="hero-video" autoplay muted loop playsinline preload="metadata" poster="{{ $homeSettings['background_image'] ?? '' }}">
          <source src="{{ $homeSettings['video_presentation']['url'] }}" type="{{ str_ends_with(parse_url($homeSettings['video_presentation']['url'], PHP_URL_PATH), '.webm') ? 'video/webm' : 'video/mp4' }}" />
        </video>
      @elseif(($homeSettings['video_presentation']['type'] ?? null) === 'embed')
        <iframe class="hero-video-embed" src="{{ $homeSettings['video_presentation']['url'] }}" title="Video de bienvenida de la institución" loading="eager" tabindex="-1" allow="autoplay; encrypted-media; picture-in-picture" aria-hidden="true"></iframe>
      @elseif(!empty($homeSettings['background_image']))
        <img class="hero-background-image" src="{{ $homeSettings['background_image'] }}" alt="" />
      @else
        <video class="hero-video" autoplay muted loop playsinline preload="metadata">
          <source src="https://videos.pexels.com/video-files/29096783/12571522_1920_1080_30fps.mp4" type="video/mp4" />
        </video>
      @endif
      <div class="hero-orb orb-1"></div>
      <div class="hero-orb orb-2"></div>
      <div class="hero-orb orb-3"></div>
      <div class="hero-grid"></div>
    </div>

    {{-- Layout 3 columnas: btn izquierdo | contenido | btn derecho --}}
    <div class="hero-layout">

      {{-- Botón izquierdo: Publicaciones --}}
      <div class="hero-side hero-side-left">
        <a href="{{ $homeSettings['left_button_url'] }}" class="btn btn-side btn-outline">
          <i class="fas fa-book-open" aria-hidden="true"></i>
          <span>{{ $homeSettings['left_button_text'] }}</span>
        </a>
      </div>

      {{-- Contenido central --}}
      <div class="hero-content">
          @if($legacyHeroContent)
            {!! $inicio->content !!}
          @else
            <div style="margin-bottom:1.25rem;animation:fadeUp .7s var(--ease) both;">
              <div style="display:inline-block;padding:9px;background:rgba(255,255,255,.07);border:1.5px solid rgba(200,149,42,.45);border-radius:50%;">
                <img src="{{ $heroLogo }}" alt="Logo {{ $school['short_name'] }}"
                     style="height:110px;width:110px;object-fit:contain;filter:drop-shadow(0 6px 28px rgba(0,0,0,0.5));display:block;" />
              </div>
            </div>

            <div class="hero-badge">
              <i class="fas fa-map-marker-alt" aria-hidden="true"></i>&nbsp; {{ $homeSettings['badge'] }}
            </div>

            <h1 class="hero-title">
              {{ $homeSettings['title_prefix'] }} <em class="highlight">&ldquo;{{ $homeSettings['title_highlight'] }}&rdquo;</em>
              <br />
              <span style="font-size:.62em;font-weight:700;letter-spacing:.02em;color:rgba(255,255,255,.9);">{{ $homeSettings['institution_name'] }}</span>
            </h1>

            <div style="display:flex;align-items:center;justify-content:center;gap:.85rem;margin-bottom:1.25rem;animation:fadeUp .8s var(--ease) .3s both;">
              <span style="flex:1;max-width:60px;height:1px;background:linear-gradient(90deg,transparent,rgba(200,149,42,.75));"></span>
              <span style="font-size:.65rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--clr-gold-light);">{{ $homeSettings['motto'] }}</span>
              <span style="flex:1;max-width:60px;height:1px;background:linear-gradient(270deg,transparent,rgba(200,149,42,.75));"></span>
            </div>

            <p class="hero-subtitle" style="max-width:520px;margin-inline:auto;margin-bottom:0;">
              {!! nl2br(e($homeSettings['subtitle'])) !!}
            </p>
          @endif

      </div>

      {{-- Botón derecho: Conócenos --}}
      <div class="hero-side hero-side-right">
        <a href="{{ $homeSettings['right_button_url'] }}" class="btn btn-side btn-primary">
          <span>{{ $homeSettings['right_button_text'] }}</span>
          <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
      </div>

    </div>

    <div class="hero-scroll" aria-hidden="true">
      <span>{{ $homeSettings['scroll_text'] }}</span>
      <i class="fas fa-chevron-down"></i>
    </div>
  </section>



  {{-- ═══════════════════════════════════
       STATS BAR
  ═══════════════════════════════════ --}}
  <section class="stats-bar" aria-label="Cifras de la institución">
    <div class="container">
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-value">
            <span class="stat-number" data-target="{{ $homeSettings['students_value'] }}">0</span>
            <span class="stat-plus">+</span>
          </div>
          <span class="stat-label">{{ $homeSettings['students_label'] }}</span>
        </div>
        <div class="stat-item">
          <div class="stat-value">
            <span class="stat-number" data-target="{{ $homeSettings['teachers_value'] }}">0</span>
            <span class="stat-plus">+</span>
          </div>
          <span class="stat-label">{{ $homeSettings['teachers_label'] }}</span>
        </div>
        <div class="stat-item">
          <div class="stat-value">
            <span class="stat-number" data-target="{{ $homeSettings['years_value'] }}">0</span>
            <span class="stat-plus">+</span>
          </div>
          <span class="stat-label">{{ $homeSettings['years_label'] }}</span>
        </div>
        <div class="stat-item">
          <div class="stat-value">
            <span class="stat-number" data-target="{{ $homeSettings['projects_value'] }}">0</span>
            <span class="stat-plus">+</span>
          </div>
          <span class="stat-label">{{ $homeSettings['projects_label'] }}</span>
        </div>
      </div>
    </div>
  </section>

  @include('pages.partials.section-cards', ['items' => $sectionPages->get('inicio', collect())])

  {{-- ═══════════════════════════════════
       ¿QUIÉNES SOMOS?
  ═══════════════════════════════════ --}}
  <section id="quienes-somos" class="section">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">{{ $siteSections['quienes-somos']['eyebrow'] }}</span>
        <h2 class="section-title">{{ $siteSections['quienes-somos']['title'] }}</h2>
        <div class="section-divider"></div>
      </div>
      <div class="about-grid">
        <div class="about-content reveal">
          <h3>{{ $siteSections['quienes-somos']['intro_heading'] }}</h3>
          <p>{!! nl2br(e($siteSections['quienes-somos']['intro_first'])) !!}</p>
          <p>{!! nl2br(e($siteSections['quienes-somos']['intro_second'])) !!}</p>
          <blockquote class="about-motto">
            <i class="fas fa-quote-left" aria-hidden="true"></i>
            <span>{{ $siteSections['quienes-somos']['motto'] }}</span>
            <small>{{ $siteSections['quienes-somos']['motto_caption'] }}</small>
          </blockquote>
          <div class="about-features">
            <div class="feature-item">
              <i class="fas {{ $siteSections['quienes-somos']['feature_academic_icon'] }}" aria-hidden="true"></i>
              <span>{{ $siteSections['quienes-somos']['feature_academic'] }}</span>
            </div>
            <div class="feature-item">
              <i class="fas {{ $siteSections['quienes-somos']['feature_values_icon'] }}" aria-hidden="true"></i>
              <span>{{ $siteSections['quienes-somos']['feature_values'] }}</span>
            </div>
            <div class="feature-item">
              <i class="fas {{ $siteSections['quienes-somos']['feature_innovation_icon'] }}" aria-hidden="true"></i>
              <span>{{ $siteSections['quienes-somos']['feature_innovation'] }}</span>
            </div>
            <div class="feature-item">
              <i class="fas {{ $siteSections['quienes-somos']['feature_community_icon'] }}" aria-hidden="true"></i>
              <span>{{ $siteSections['quienes-somos']['feature_community'] }}</span>
            </div>
          </div>
          <figure class="about-inline-photo">
            <img src="{{ $siteSections['quienes-somos']['media']['inline_image'] }}" alt="Familias y docentes participan en una reunión de la institución educativa secundaria"
                 loading="lazy" />
            <figcaption>{{ $siteSections['quienes-somos']['inline_caption'] }}</figcaption>
          </figure>
        </div>

        <div class="about-visual reveal">
          <figure class="about-photo">
            <img src="{{ $siteSections['quienes-somos']['media']['main_image'] }}" alt="Estudiantes, docentes y familias de la institución educativa secundaria"
                 loading="lazy" />
            <figcaption>{{ $siteSections['quienes-somos']['main_caption'] }}</figcaption>
          </figure>

          <div class="visual-card card-mission">
            <i class="fas {{ $siteSections['quienes-somos']['mission_icon'] }}" aria-hidden="true"></i>
            <h4>{{ $siteSections['quienes-somos']['mission_heading'] }}</h4>
            <p>{{ $siteSections['quienes-somos']['mission'] }}</p>
          </div>
          <div class="visual-card card-vision">
            <i class="fas {{ $siteSections['quienes-somos']['vision_icon'] }}" aria-hidden="true"></i>
            <h4>{{ $siteSections['quienes-somos']['vision_heading'] }}</h4>
            <p>{{ $siteSections['quienes-somos']['vision'] }}</p>
          </div>
          <div class="visual-card card-values">
            <i class="fas {{ $siteSections['quienes-somos']['values_icon'] }}" aria-hidden="true"></i>
            <h4>{{ $siteSections['quienes-somos']['values_heading'] }}</h4>
            <p>{{ $siteSections['quienes-somos']['values'] }}</p>
          </div>
        </div>
      </div>
      @if(isset($quienes) && $quienes->content)
        <div class="about-grid">{!! $quienes->content !!}</div>
      @endif
      @include('pages.partials.section-cards', ['items' => $sectionPages->get('quienes-somos', collect())])
    </div>
  </section>

  {{-- ═══════════════════════════════════
       PROYECTO IJENT
  ═══════════════════════════════════ --}}
  <section id="proyecto" class="section section-dark ijent-project">
    <div class="container">
      <div class="section-header ijent-project-header">
        <div class="ijent-project-heading">
          <span class="section-tag"><i class="fas fa-lightbulb" aria-hidden="true"></i> {{ $siteSections['proyecto']['eyebrow'] }}</span>
          <h2 class="section-title">{{ $siteSections['proyecto']['title'] }}</h2>
          <div class="section-divider"></div>
        </div>
        <p class="section-desc">
          {{ $siteSections['proyecto']['description'] }}
        </p>
      </div>

      {{-- La tarjeta "Vista general" se renderiza dentro del drive-browser para
           que actúe como el primer slide (se muestra/oculta según navegación). --}}

      <div class="drive-browser ijent-drive-browser reveal" role="region" aria-label="Explorador de recursos: {{ $siteSections['proyecto']['root_label'] }}">
        <div class="drive-browser-bar">
          <div class="drive-path" id="drivePath" aria-live="polite">
            <span class="path-segment path-root" role="button" tabindex="0"
                  onclick="app.resetDrive()" aria-label="Ir a raíz del proyecto">
              <i class="fas fa-folder-open" aria-hidden="true"></i> {{ $siteSections['proyecto']['root_label'] }}
            </span>
          </div>
          <div class="drive-browser-nav" aria-label="Navegación entre secciones IJENT">
            <button class="drive-section-btn" id="drivePrevSection" type="button" aria-label="Sección anterior">
              <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button class="drive-section-btn" id="driveNextSection" type="button" aria-label="Sección siguiente">
              <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
          </div>
          <button class="drive-back-btn" id="driveBack" style="display:none"
                  aria-label="Volver a la carpeta anterior">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver
          </button>
        </div>

        <!-- Contenedor del slide: aquí se inserta la tarjeta 'Vista general' -->
        <div id="projectSlide" class="project-highlight reveal">
          <div class="project-highlight-media">
            <img src="{{ $siteSections['proyecto']['media']['image'] }}" data-full-src="{{ $siteSections['proyecto']['media']['image'] }}" alt="Estudiantes de secundaria aprenden y exploran el entorno con sus docentes"
                 loading="lazy" />
          </div>
          <div class="project-highlight-content">
            <span class="project-highlight-tag"><i class="fas {{ $siteSections['proyecto']['highlight_tag_icon'] }}" aria-hidden="true"></i> {{ $siteSections['proyecto']['highlight_tag'] }}</span>
            <h3>{{ $siteSections['proyecto']['highlight_title'] }}</h3>
            <p>{{ $siteSections['proyecto']['highlight_description'] }}</p>
            <div class="project-highlight-pills">
              <span><i class="fas {{ $siteSections['proyecto']['pill_1_icon'] }}" aria-hidden="true"></i> {{ $siteSections['proyecto']['pill_1'] }}</span>
              <span><i class="fas {{ $siteSections['proyecto']['pill_2_icon'] }}" aria-hidden="true"></i> {{ $siteSections['proyecto']['pill_2'] }}</span>
              <span><i class="fas {{ $siteSections['proyecto']['pill_3_icon'] }}" aria-hidden="true"></i> {{ $siteSections['proyecto']['pill_3'] }}</span>
            </div>
            <p class="ijent-explore-hint">
              <i class="fas fa-arrow-right" aria-hidden="true"></i>
              {{ $siteSections['proyecto']['explore_hint'] }}
            </p>
          </div>
        </div>

        <div class="drive-grid" id="driveContent" aria-live="polite">
          <div class="drive-empty-state" style="text-align:center;">
            <div class="spinner" aria-hidden="true"></div>
            <p>{{ $siteSections['proyecto']['drive_loading_text'] }}</p>

            <!-- Fallback preview: imagen grande + pie de página con título y descripción -->
            <figure class="drive-preview">
              <div class="drive-preview-inner">
                <img id="drivePreviewImg" src="{{ $siteSections['proyecto']['media']['image'] }}" data-full-src="{{ $siteSections['proyecto']['media']['image'] }}" alt="Preview del recurso" loading="lazy" />
                <figcaption id="drivePreviewCaption">
                  <h3 id="drivePreviewTitle">{{ $siteSections['proyecto']['drive_fallback_title'] }}</h3>
                  <p id="drivePreviewText">{{ $siteSections['proyecto']['drive_fallback_description'] }}</p>
                  <div style="margin-top:.8rem;text-align:right;"><a id="driveOpenLink" href="#" class="btn btn-outline" style="font-weight:800;color:var(--clr-navy);">{{ $siteSections['proyecto']['drive_open_label'] }}</a></div>
                </figcaption>
              </div>
            </figure>

            <!-- Lightbox modal (with caption) -->
            <div id="driveModal" class="drive-modal" aria-hidden="true">
              <div class="drive-modal-backdrop" data-close="true"></div>
              <div class="drive-modal-content" role="dialog" aria-modal="true" aria-label="Vista previa ampliada">
                <div class="drive-modal-panel">
                  <button class="drive-modal-close" aria-label="Cerrar" data-close="true">✕</button>
                  <div class="drive-modal-body">
                    <img id="driveModalImg" class="drive-modal-img" src="{{ $siteSections['proyecto']['media']['image'] }}" alt="Preview ampliada" />
                    <div id="driveModalCaption" class="drive-modal-caption" style="background:rgba(255,255,255,0.96);padding:1rem;border-radius:8px;">
                      <h3 id="driveModalTitle" style="margin:0 0 .4rem;font-family:var(--font-serif);font-size:1.2rem;color:var(--clr-navy);">{{ $siteSections['proyecto']['drive_fallback_title'] }}</h3>
                      <p id="driveModalText" style="margin:0;color:rgba(7,20,40,.78);">{{ $siteSections['proyecto']['drive_fallback_description'] }}</p>
                      <div style="margin-top:.6rem;text-align:right;"><a id="driveModalOpenLink" href="#" class="btn btn-outline" style="font-weight:800;color:var(--clr-navy);">{{ $siteSections['proyecto']['drive_open_label'] }}</a></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="videoPreviewModal" class="video-preview-modal" aria-hidden="true">
        <div class="video-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="videoPreviewTitle">
          <header class="video-preview-header">
            <h2 id="videoPreviewTitle"></h2>
            <button id="videoPreviewClose" class="video-preview-close" type="button" aria-label="Cerrar reproductor">
              <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
          </header>
          <div class="video-preview-stage">
            <video id="videoPreviewPlayer" controls playsinline preload="metadata">
              Tu navegador no puede reproducir este video.
            </video>
          </div>
        </div>
      </div>
      <div id="imagePreviewModal" class="image-preview-modal" aria-hidden="true">
        <div class="image-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="imagePreviewTitle">
          <header class="image-preview-header">
            <h2 id="imagePreviewTitle"></h2>
            <button id="imagePreviewClose" class="image-preview-close" type="button" aria-label="Cerrar imagen">
              <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
          </header>
          <div class="image-preview-stage">
            <img id="imagePreviewPlayer" alt="">
          </div>
          <div class="image-preview-caption">
            <p id="imagePreviewDescription" hidden></p>
          </div>
        </div>
      </div>
      <div id="pdfPreviewModal" class="pdf-preview-modal" aria-hidden="true">
        <div class="pdf-preview-dialog" role="dialog" aria-modal="true" aria-label="Vista previa de documento PDF">
          <header class="pdf-preview-header">
            <h2>Documento PDF</h2>
            <button id="pdfPreviewClose" class="pdf-preview-close" type="button" aria-label="Cerrar documento PDF">
              <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
          </header>
          <div class="pdf-preview-stage">
            <iframe id="pdfPreviewFrame" title="Vista previa de documento PDF"></iframe>
          </div>
          <div class="pdf-preview-caption">
            <p id="pdfPreviewDescription" hidden></p>
          </div>
        </div>
      </div>
      @include('pages.partials.section-cards', ['items' => $sectionPages->get('proyecto', collect())])
    </div>
  </section>

  {{-- ═══════════════════════════════════
       NOVEDADES
  ═══════════════════════════════════ --}}
  <section id="novedades" class="section news-elegant">
    <div class="container">
      <div class="section-header">
        <span class="section-tag"><i class="fas fa-newspaper" aria-hidden="true"></i> {{ $siteSections['novedades']['eyebrow'] }}</span>
        <h2 class="section-title">{{ $siteSections['novedades']['title'] }}</h2>
        <div class="section-divider"></div>
        <p class="section-desc news-section-desc">
          {{ $siteSections['novedades']['description'] }}
        </p>
      </div>
    </div>

    <div class="news-full-bleed">
      <div class="news-grid-wrap">
        <div class="news-grid-shell">
          <div class="news-grid-track">
            <ul class="news-grid" id="novedadesGrid">
            @php
              // Use provided news data when available, otherwise use the editable fallback cards.
              $items = $novedades ?? null;
              if(!$items || count((array)$items) === 0){
                $items = [];
                $newsCount = min(30, max(1, (int) ($siteSections['novedades']['news_count'] ?? 6)));
                $today = now()->toDateString();
                foreach ($newsCount > 0 ? range(1, $newsCount) : [] as $newsIndex) {
                  $startsAt = $siteSections['novedades']['news_'.$newsIndex.'_starts_at'] ?? '';
                  $endsAt = $siteSections['novedades']['news_'.$newsIndex.'_ends_at'] ?? '';
                  if (($startsAt && $startsAt > $today) || ($endsAt && $endsAt < $today)) {
                    continue;
                  }
                  $newsTitle = $siteSections['novedades']['news_'.$newsIndex.'_title'] ?? '';
                  if (blank($newsTitle)) {
                    continue;
                  }
                  $items[] = [
                    'date' => $siteSections['novedades']['news_'.$newsIndex.'_date'],
                    'title' => $newsTitle,
                    'excerpt' => $siteSections['novedades']['news_'.$newsIndex.'_excerpt'],
                    'tag' => $siteSections['novedades']['news_'.$newsIndex.'_tag'],
                    'icon' => $siteSections['novedades']['news_'.$newsIndex.'_icon'],
                    'image' => $siteSections['novedades']['media']['news_'.$newsIndex.'_image'],
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                  ];
                }
              }
              $items = array_values((array)$items);
            @endphp

            @foreach($items as $item)
            @php
              // Prefer an explicit image on the item; otherwise use images from public/images (round-robin)
              $provided = $item['image'] ?? ($item->image ?? null);
              $localImages = [
                'images/comunidad-educativa.jpg',
                'images/exposicion-estudiantil.jpg',
                'images/formacion-docente.jpg',
                'images/encuentro-con-familias.jpg',
                'images/aprendizaje-en-el-aula.jpg',
                'images/salida-pedagogica.jpg',
              ];
              $idx = isset($loop) ? $loop->index : 0;
              if(!empty($provided)){
                // if provided is a full URL, keep it; otherwise treat as relative path under public/
                if(preg_match('/^https?:\/\//', $provided)){
                  $img = $provided;
                } else {
                  $img = asset($provided);
                }
              } else {
                $img = asset($localImages[$idx % count($localImages)]);
              }

              $title = $item['title'] ?? ($item->title ?? 'Próxima novedad');
              $date = $item['date'] ?? ($item->date ?? '');
              $excerpt = $item['excerpt'] ?? ($item->excerpt ?? 'Contenido próximamente.');
              $tag = $item['tag'] ?? ($item->tag ?? '');
              $icon = $item['icon'] ?? ($item->icon ?? 'fa-newspaper');
              if (! array_key_exists((string) $icon, \App\Support\SiteSectionSettings::iconChoices())) {
                $icon = 'fa-newspaper';
              }
              $link = $item['link'] ?? ($item->link ?? '#');
            @endphp
            <li class="news-grid-item">
              <article class="news-card" data-managed-news="true">
              <div class="news-card-cover" style="background-image:linear-gradient(180deg,rgba(6,20,40,.02),rgba(6,20,40,.06));">
                <img src="{{ $img }}" data-full-src="{{ $img }}" alt="{{ $title }}" loading="lazy" decoding="async" />
                @if(!empty($tag))
                  <span class="tag-chip"><i class="fas {{ $icon }}" aria-hidden="true"></i> {{ $tag }}</span>
                @else
                  <span class="tag-chip"><i class="fas {{ $icon }}" aria-hidden="true"></i> Actualidad</span>
                @endif
              </div>
              <div class="news-body">
                <div class="news-date"><i class="far fa-calendar" aria-hidden="true"></i> {{ $date }}</div>
                <h3>{{ $title }}</h3>
                <p>{{ $excerpt }}</p>
              </div>
              <div class="news-footer">
                <span class="news-tag">{{ $tag }}</span>
                <a class="news-cta" href="{{ $link }}">Ver más <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
              </div>
              </article>
            </li>
            @endforeach
            </ul>
            <div class="news-carousel-controls" role="group" aria-label="Controles de novedades">
              <span class="news-carousel-status" id="novedadesStatus" aria-live="polite">01 / 02</span>
              <button class="news-carousel-button" id="novedadesPrev" type="button" aria-label="Ver novedades anteriores" disabled>
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
              </button>
              <button class="news-carousel-button" id="novedadesNext" type="button" aria-label="Ver novedades siguientes">
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
    </div>
    @include('pages.partials.section-cards', ['items' => $sectionPages->get('novedades', collect())])
  </section>

  {{-- ═══════════════════════════════════
       PUBLICACIONES
  ═══════════════════════════════════ --}}
  <section id="publicaciones" class="section section-alt publications-elegant">
    <div class="container">
      <div class="section-header">
        <span class="section-tag"><i class="fas fa-book-open" aria-hidden="true"></i> {{ $siteSections['publicaciones']['eyebrow'] }}</span>
        <h2 class="section-title">{{ $siteSections['publicaciones']['title'] }}</h2>
        <div class="section-divider" aria-hidden="true"></div>
        <p class="publications-intro">Proyectos de aula, aprendizajes y actividades que comparten estudiantes y docentes de nuestra institución secundaria.</p>
      </div>

      @php
        $publicationSlides = [];
        $publicationToday = now()->toDateString();
        $publicationCount = (int) ($siteSections['publicaciones']['publication_count'] ?? 3);
        for ($slideNumber = 1; $slideNumber <= $publicationCount; $slideNumber++) {
            if (! filled($siteSections['publicaciones']['slide_'.$slideNumber.'_title'] ?? '')) {
                continue;
            }
            $slideStartsAt = $siteSections['publicaciones']['slide_'.$slideNumber.'_starts_at'] ?? '';
            $slideEndsAt = $siteSections['publicaciones']['slide_'.$slideNumber.'_ends_at'] ?? '';
            if ((! filled($slideStartsAt) || $slideStartsAt <= $publicationToday)
                && (! filled($slideEndsAt) || $slideEndsAt >= $publicationToday)) {
                $publicationSlides[] = $slideNumber;
            }
        }
      @endphp
      <div id="publicacionesSliderSection" class="publications-showcase">
        <div class="publicaciones-slider-wrap">
          <div id="publicSlider" class="publicaciones-slider" role="region" aria-roledescription="carrusel" aria-label="Publicaciones destacadas">
            @foreach($publicationSlides as $slideIndex => $slide)
            @php
              $slideTitle = $siteSections['publicaciones']['slide_'.$slide.'_title'];
              $slideDescription = $siteSections['publicaciones']['slide_'.$slide.'_description'];
              $slideIcon = $siteSections['publicaciones']['slide_'.$slide.'_icon'];
              $slideImage = $siteSections['publicaciones']['media']['slide_'.$slide.'_image'];
            @endphp
            <article class="slide {{ $loop->first ? 'active' : '' }}" data-title="{{ $slideTitle }}" data-subtitle="{{ $slideDescription }}" role="group" aria-roledescription="diapositiva" aria-label="{{ $slideIndex + 1 }} de {{ count($publicationSlides) }}">
              <img src="{{ $slideImage }}" alt="{{ $slideTitle }}" loading="lazy" />
              <div class="slide-desc">
                <span class="publication-slide-index">{{ str_pad($slideIndex + 1, 2, '0', STR_PAD_LEFT) }} <span>/ {{ str_pad(count($publicationSlides), 2, '0', STR_PAD_LEFT) }}</span></span>
                <span class="publication-slide-kicker"><i class="fas {{ $slideIcon }}" aria-hidden="true"></i> Experiencias que inspiran</span>
                <h3>{{ $slideTitle }}</h3>
                <p>{{ $slideDescription }}</p>
              </div>
            </article>
            @endforeach
          </div>

          @if(count($publicationSlides))
          <button class="slider-prev" type="button" aria-label="Ver publicación anterior"><i class="fas fa-arrow-left" aria-hidden="true"></i></button>
          <button class="slider-next" type="button" aria-label="Ver publicación siguiente"><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
          <div class="slider-dots" aria-label="Seleccionar publicación"></div>
          @else
          <div class="publications-empty-state" role="status">
            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            <h3>Próximamente nuevas publicaciones</h3>
            <p>Vuelve pronto para descubrir las novedades de nuestra comunidad educativa.</p>
          </div>
          @endif
        </div>
        <div class="publications-showcase-caption" aria-hidden="true">
          <span><i class="fas fa-sparkles"></i> Conoce lo que nos inspira</span>
          <span>Comunidad · Aprendizaje · Futuro</span>
        </div>
      </div>
      @include('pages.partials.section-cards', ['items' => $sectionPages->get('publicaciones', collect())])
    </div>
  </section>

@push('scripts')
<script>
  (function(){
    const slider = document.getElementById('publicSlider');
    if (!slider) return;
    const slides = Array.from(slider.querySelectorAll('.slide'));
    if (!slides.length) return;
    const prev = document.querySelector('.slider-prev');
    const next = document.querySelector('.slider-next');
    const dotsWrap = document.querySelector('.slider-dots');
    let idx = 0; let interval = null; const intervalMs = 5000;

    function buildDots(){
      dotsWrap.innerHTML = '';
      slides.forEach((s,i)=>{
        const d = document.createElement('button'); d.className='dot'; d.setAttribute('aria-label','Ir a slide '+(i+1));
        d.addEventListener('click', ()=>{ goTo(i); });
        dotsWrap.appendChild(d);
      });
      updateDots();
    }

    function updateDots(){
      Array.from(dotsWrap.children).forEach((d,i)=> d.classList.toggle('active', i===idx));
    }

    function showSlide(i){
      slides.forEach((s,si)=> s.classList.toggle('active', si===i));
      idx = i; updateDots();
    }

    function goTo(i){ clearInterval(interval); showSlide((i+slides.length)%slides.length); start(); }
    function prevSlide(){ goTo(idx-1); }
    function nextSlide(){ goTo(idx+1); }

    function start(){ interval = setInterval(()=>{ showSlide((idx+1)%slides.length); }, intervalMs); }
    function stop(){ if(interval) clearInterval(interval); interval = null; }

    prev.addEventListener('click', prevSlide);
    next.addEventListener('click', nextSlide);
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);

    buildDots(); start();
  })();
</script>
<script>
  // Lightbox for image preview (local-only)
  (function(){
    function initLightbox(){
      console.debug('[lightbox] initLightbox start');

      function ensureModalExists(){
        const existingModal = document.getElementById('driveModal');
        if(existingModal){
          if(existingModal.parentElement !== document.body) document.body.appendChild(existingModal);
          return existingModal;
        }
        const modal = document.createElement('div');
        modal.id = 'driveModal'; modal.className = 'drive-modal'; modal.setAttribute('aria-hidden','true');
        modal.style.display = 'none';
        modal.innerHTML = `
          <div class="drive-modal-backdrop" data-close="true"></div>
          <div class="drive-modal-content" role="dialog" aria-modal="true" aria-label="Vista previa ampliada">
            <div class="drive-modal-panel">
              <button class="drive-modal-close" aria-label="Cerrar" data-close="true">✕</button>
              <button id="driveModalPrev" class="drive-modal-nav drive-modal-prev" aria-label="Anterior" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:48px;height:48px;border-radius:8px;background:rgba(255,255,255,0.95);box-shadow:0 6px 18px rgba(7,20,40,.25);display:flex;align-items:center;justify-content:center;">‹</button>
              <button id="driveModalNext" class="drive-modal-nav drive-modal-next" aria-label="Siguiente" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:48px;height:48px;border-radius:8px;background:rgba(255,255,255,0.95);box-shadow:0 6px 18px rgba(7,20,40,.25);display:flex;align-items:center;justify-content:center;">›</button>
              <div class="drive-modal-body">
                <img id="driveModalImg" class="drive-modal-img" src="" alt="Preview ampliada" style="max-width:100%;height:auto;border-radius:6px;" />
                <div id="driveModalCaption" class="drive-modal-caption" style="background:rgba(255,255,255,0.96);padding:1rem;border-radius:8px;">
                  <h3 id="driveModalTitle" style="margin:0 0 .4rem;font-family:var(--font-serif);font-size:1.2rem;color:var(--clr-navy);"></h3>
                  <p id="driveModalText" style="margin:0;color:rgba(7,20,40,.78);"></p>
                  <div style="margin-top:.6rem;text-align:right;"><a id="driveModalOpenLink" href="#" class="btn btn-outline" style="font-weight:800;color:var(--clr-navy);">Abrir</a></div>
                </div>
              </div>
            </div>
          </div>`;
        document.body.appendChild(modal);
        // do not attach close handlers here; they are attached after close() is defined to ensure
        // the shared close() logic (which restores page arrows) runs for all close paths.
        return modal;
      }

      const modalEl = ensureModalExists();
      let currentModalIndex = -1;

      function getNewsCards(){
        return Array.from(document.querySelectorAll('.news-card'));
      }

      function openAtIndex(index){
        const cards = getNewsCards();
        if(index < 0 || index >= cards.length) return;
        const card = cards[index];
        const data = extractDataFrom(card);
        modalEl.classList.add('news-detail-modal');
        openWith(data.src, data.title, data.text, data.href);
        currentModalIndex = index;
        updateModalNav();
      }

      function openWith(src, title, text, openHref){
        console.debug('[lightbox] openWith', { src, title, text, openHref });
        const modal = document.getElementById('driveModal') || modalEl;
        const modalImg = document.getElementById('driveModalImg');
        const modalTitle = document.getElementById('driveModalTitle');
        const modalText = document.getElementById('driveModalText');
        const modalOpenLink = document.getElementById('driveModalOpenLink');
        if(modalImg){
          modalImg.src = src || '';
          modalImg.alt = title ? `Imagen de ${title}` : 'Imagen ampliada';
        }
        if(modalTitle) modalTitle.textContent = title || '';
        if(modalText) modalText.textContent = text || '';
        if(modalOpenLink){
          if(openHref && openHref !== '#'){
            modalOpenLink.href = openHref;
            modalOpenLink.style.display = '';
          } else {
            modalOpenLink.style.display = 'none';
          }
        }
        try{
          modal.classList.add('open');
          modal.setAttribute('aria-hidden','false');
          modal.style.display = 'flex';
          modal.style.zIndex = '10000';
          document.body.style.overflow = 'hidden';
          // hide page directional buttons (Glide arrows, slider arrows) while modal is open
          hidePageDirectionalButtons(true);
          setTimeout(()=>{ try{ modal.scrollIntoView({block:'center', behavior:'smooth'}); }catch(e){} }, 60);
        }catch(e){ console.debug('[lightbox] failed to show modal', e); }
        updateModalNav();
      }

      function close(){
        const modal = document.getElementById('driveModal') || modalEl;
        if(!modal) return;
        modal.classList.remove('open');
        modal.classList.remove('news-detail-modal');
        modal.setAttribute('aria-hidden','true');
        modal.style.display='none';
        document.body.style.overflow = '';
        // restore page directional buttons
        hidePageDirectionalButtons(false);
        // fallback: ensure arrows are visible in case they were removed/Glide mutated DOM
        try{
          const sel = ['.publicaciones-slider-wrap .slider-prev', '.publicaciones-slider-wrap .slider-next'];
          sel.forEach(s => Array.from(document.querySelectorAll(s)).forEach(el => {
            try{
              el.style.visibility = '';
              el.style.pointerEvents = '';
              el.style.display = '';
              el.removeAttribute('aria-hidden');
              if(el.disabled) el.disabled = false;
              el.setAttribute('aria-disabled', 'false');
            }catch(e){}
          }));
        }catch(e){ console.debug('[lightbox] restore fallback error', e); }
      }

      function extractDataFrom(el){
        if(!el) return {};
        const img = el.querySelector('img');
        const src = img ? (img.dataset && img.dataset.fullSrc ? img.dataset.fullSrc : img.src) : '';
        const title = el.querySelector('h3') ? el.querySelector('h3').textContent.trim() : '';
        const text = el.querySelector('p') ? el.querySelector('p').textContent.trim() : '';
        const link = el.querySelector('a[href]') ? el.querySelector('a[href]').getAttribute('href') : '#';
        return { src, title, text, href: link };
      }

      // attach to news cards (direct + delegated as fallback)
      document.querySelectorAll('.news-card').forEach((card, idx)=>{
        try{ card.style.cursor = 'pointer'; }catch(e){}
        card.addEventListener('click', (e)=>{
          console.debug('[lightbox] card clicked');
          if(e.target.closest('a') || e.target.closest('button')) return;
          openAtIndex(idx);
        });
      });
      document.querySelectorAll('.news-cta').forEach((link, idx) => {
        link.addEventListener('click', function(e){
          console.debug('[lightbox] cta clicked');
          e.preventDefault(); e.stopPropagation();
          // open modal focused on this card
          const card = this.closest('.news-card');
          const cards = getNewsCards();
          const index = cards.indexOf(card);
          if(index >= 0) openAtIndex(index);
          else {
            const data = extractDataFrom(card || this.parentElement);
            openWith(data.src, data.title, data.text, this.getAttribute('href') || data.href);
          }
        });
      });

      // delegated global fallback
      document.addEventListener('click', function(e){
        const cta = e.target.closest ? e.target.closest('.news-cta') : null;
        if(cta){ e.preventDefault(); e.stopPropagation(); const card = cta.closest('.news-card'); const cards = getNewsCards(); const index = cards.indexOf(card); if(index>=0) openAtIndex(index); else { const data = extractDataFrom(card || cta.parentElement); openWith(data.src, data.title, data.text, cta.getAttribute('href') || data.href); } return; }
        const card = e.target.closest ? e.target.closest('.news-card') : null;
        if(card){ if(e.target.closest('a') || e.target.closest('button')) return; const cards = getNewsCards(); const index = cards.indexOf(card); if(index>=0) openAtIndex(index); else { const data = extractDataFrom(card); openWith(data.src, data.title, data.text, data.href); } }
      });

      // Project IJENT preview
      const projectImg = document.querySelector('#projectSlide .project-highlight-media img');
      const projectContainer = document.getElementById('projectSlide');
      if(projectImg && projectContainer){
        projectImg.style.cursor = 'zoom-in';
        projectImg.addEventListener('click', ()=>{ const data = extractDataFrom(projectContainer); openWith(data.src || projectImg.src, data.title || (projectContainer.querySelector('h3') ? projectContainer.querySelector('h3').textContent : ''), data.text || '', data.href); currentModalIndex = -1; updateModalNav(); });
      }

      const fallbackPreviewImg = document.getElementById('drivePreviewImg');
      const fallbackPreview = document.querySelector('.drive-preview');
      if(fallbackPreviewImg && fallbackPreview){
        fallbackPreviewImg.style.cursor = 'zoom-in';
        fallbackPreviewImg.addEventListener('click', ()=>{ const data = extractDataFrom(fallbackPreview); openWith(data.src || fallbackPreviewImg.src, data.title || document.getElementById('drivePreviewTitle')?.textContent || '', data.text || document.getElementById('drivePreviewText')?.textContent || '', document.getElementById('driveOpenLink')?.getAttribute('href') || '#'); currentModalIndex = -1; updateModalNav(); });
      }

      function updateModalNav(){
        const prevBtn = document.getElementById('driveModalPrev');
        const nextBtn = document.getElementById('driveModalNext');
        const cards = getNewsCards();
        if(!prevBtn || !nextBtn) return;
        if(currentModalIndex === -1){
          prevBtn.disabled = true; prevBtn.setAttribute('aria-disabled','true'); prevBtn.style.pointerEvents='none';
          nextBtn.disabled = true; nextBtn.setAttribute('aria-disabled','true'); nextBtn.style.pointerEvents='none';
          return;
        }
        prevBtn.disabled = currentModalIndex <= 0;
        prevBtn.setAttribute('aria-disabled', prevBtn.disabled ? 'true' : 'false');
        prevBtn.style.pointerEvents = prevBtn.disabled ? 'none' : '';
        nextBtn.disabled = currentModalIndex >= (cards.length - 1);
        nextBtn.setAttribute('aria-disabled', nextBtn.disabled ? 'true' : 'false');
        nextBtn.style.pointerEvents = nextBtn.disabled ? 'none' : '';
      }

      // modal nav handlers
      document.addEventListener('click', function modalNavHandler(e){
        const prev = e.target.closest ? e.target.closest('#driveModalPrev') : null;
        const next = e.target.closest ? e.target.closest('#driveModalNext') : null;
        if(prev){ e.preventDefault(); e.stopPropagation(); if(currentModalIndex > 0) openAtIndex(currentModalIndex - 1); return; }
        if(next){ e.preventDefault(); e.stopPropagation(); const cards = getNewsCards(); if(currentModalIndex < cards.length - 1) openAtIndex(currentModalIndex + 1); return; }
      });

      // keyboard navigation when modal open
      document.addEventListener('keydown', function modalKeyNav(e){
        const modal = document.getElementById('driveModal');
        if(!modal || modal.getAttribute('aria-hidden') === 'true') return;
        if(e.key === 'ArrowLeft'){ if(currentModalIndex > 0) openAtIndex(currentModalIndex - 1); }
        if(e.key === 'ArrowRight'){ const cards = getNewsCards(); if(currentModalIndex < cards.length - 1) openAtIndex(currentModalIndex + 1); }
      });

      // ensure close via backdrop/button
      const existingModal = document.getElementById('driveModal');
      if(existingModal){ existingModal.addEventListener('click', (e)=>{ if(e.target.dataset.close==='true' || e.target===existingModal) close(); }); }
      document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') close(); });
    }

    // Hide/show page directional buttons (Glide arrows and simple sliders)
    function hidePageDirectionalButtons(hide){
      try{
        const sel = ['.publicaciones-slider-wrap .slider-prev', '.publicaciones-slider-wrap .slider-next'];
        sel.forEach(s => Array.from(document.querySelectorAll(s)).forEach(el => {
          if(hide){
            // store previous inline styles/attributes
            el.dataset._prevVisibility = el.style.visibility || '';
            el.dataset._prevDisplay = el.style.display || '';
            el.dataset._prevPointer = el.style.pointerEvents || '';
            if(el.hasAttribute('aria-hidden')) el.dataset._prevAria = el.getAttribute('aria-hidden');
            // hide visually and prevent interaction
            el.style.visibility = 'hidden';
            el.style.pointerEvents = 'none';
            el.setAttribute('aria-hidden','true');
          } else {
            // restore previous values
            el.style.visibility = (el.dataset._prevVisibility !== undefined) ? el.dataset._prevVisibility : '';
            el.style.display = (el.dataset._prevDisplay !== undefined) ? el.dataset._prevDisplay : '';
            el.style.pointerEvents = (el.dataset._prevPointer !== undefined) ? el.dataset._prevPointer : '';
            if(el.dataset._prevAria !== undefined){ el.setAttribute('aria-hidden', el.dataset._prevAria); } else { el.removeAttribute('aria-hidden'); }
            delete el.dataset._prevVisibility; delete el.dataset._prevDisplay; delete el.dataset._prevPointer; delete el.dataset._prevAria;
          }
        }));
        console.debug('[lightbox] hidePageDirectionalButtons', { hide });
      }catch(e){ console.debug('[lightbox] hidePageDirectionalButtons error', e); }
    }

    // Ensure initialization after DOM is ready. If DOMContentLoaded already fired, init immediately.
    if(document.readyState === 'complete' || document.readyState === 'interactive'){
      setTimeout(initLightbox, 30);
    } else {
      document.addEventListener('DOMContentLoaded', initLightbox, { once: true });
    }
  })();
</script>
  <script src="{{ asset('js/accordion.js') }}"></script>
@endpush

@endsection
