<!DOCTYPE html>
<html lang="es">
@php
  $school = config('school') ?? [];
  $footerSettings = \App\Support\SiteSectionSettings::values('footer');
@endphp
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Sistema web institucional — {{ $school['name'] }}" />
  <title>{{ trim($__env->yieldContent('page_title')) !== '' ? trim($__env->yieldContent('page_title')).' — ' : '' }}{{ $school['short_name'] }} — {{ $school['name'] }}</title>

  <!-- CSRF Token (para peticiones JS futuras) -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Estilos propios -->
  <!-- Glide.js styles for carousels (CDN) - restored to keep original design -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.5.2/dist/css/glide.core.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.5.2/dist/css/glide.theme.min.css" />
  <!-- Carousel styles (local) - main stylesheet -->
  @php
    $cssVersion = file_exists(public_path('css/styles.css')) ? filemtime(public_path('css/styles.css')) : time();
  @endphp
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}?v={{ $cssVersion }}" />

  <!-- Variables CSS dinámicas desde config -->
  <style>
    :root {
      /* Primary palette from config (escudo) */
      --clr-navy: {{ $school['colors']['primary'] }};          /* verde */
      --clr-gold:  {{ $school['colors']['secondary'] }};       /* dorado */
      --clr-tertiary: {{ $school['colors']['tertiary'] ?? '#c41e1e' }}; /* rojo */

      /* Convenience aliases / contrast pairs */
      --clr-primary: var(--clr-navy);
      --clr-primary-contrast: #ffffff;
      --clr-accent: var(--clr-gold);
      --clr-accent-contrast: #071428;
      --clr-danger: var(--clr-tertiary);
    }
  </style>

  @stack('styles')
</head>
<body class="{{ request()->is('admin*') ? 'admin-app-body' : '' }}">

  <!-- LOADER -->
  <div id="loader" class="loader" role="status" aria-label="Cargando">
    <div class="loader-inner">
      <div class="loader-ring"></div>
      <span class="loader-text">Cargando</span>
    </div>
  </div>

  <!-- NAVBAR -->
  <header>
    <nav id="navbar" class="navbar{{ request()->is('admin*') ? ' admin-premium-navbar' : '' }}" role="navigation" aria-label="Navegación principal">
      <div class="nav-container">

        <a href="{{ request()->is('admin*') ? route('admin.dashboard') : route('home').'#inicio' }}" class="nav-brand" aria-label="Inicio — {{ $school['name'] }}">
          <div class="brand-logo" aria-hidden="true">
            <img src="{{ asset('images/LOGO.png') }}" alt="Logo {{ $school['short_name'] }}" style="height:42px;width:42px;object-fit:contain;border-radius:50%;" />
          </div>
          <div class="brand-text">
            <span class="brand-main">{{ $school['short_name'] }}</span>
            <span class="brand-sub">{{ $school['name'] }}</span>
          </div>
        </a>

        <button class="mobile-toggle" id="mobileToggle"
                aria-label="Abrir menú" aria-expanded="false" aria-controls="navMenu">
          <span></span><span></span><span></span>
        </button>

        <ul class="nav-menu" id="navMenu" role="menubar">
          @if(request()->is('admin*'))
            <li role="none">
              <a href="{{ route('home') }}" class="nav-link admin-site-link" role="menuitem">
                <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                <span class="nav-link-text">Ver sitio</span>
              </a>
            </li>
            <li class="admin-nav-logout" role="none">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link" role="menuitem">
                  <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                  <span class="nav-link-text">Cerrar sesión</span>
                </button>
              </form>
            </li>
          @else
            @php
              $navigationPages = \Illuminate\Support\Facades\Schema::hasTable('pages')
                ? \App\Models\Page::query()
                    ->where('published', true)
                    ->whereNotIn('slug', ['inicio', 'quienes-somos'])
                    ->orderBy('position')
                    ->orderBy('title')
                    ->get()
                : collect();
            @endphp
            <li role="none"><a href="{{ route('home') }}#inicio"         class="nav-link" role="menuitem">Inicio</a></li>
            <li role="none"><a href="{{ route('home') }}#quienes-somos"  class="nav-link" role="menuitem">¿Quiénes somos?</a></li>
            <li class="nav-item-dropdown proyecto" role="none">
              <button class="nav-link dropdown-trigger" aria-haspopup="true"
                      aria-expanded="false" aria-controls="proyectoDropdown" role="menuitem">
                <span class="nav-link-text">Proyecto_IJENT</span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
              </button>
              <div class="dropdown-menu" id="proyectoDropdown" role="menu" aria-label="Submenú Proyecto IJENT">
                <div class="dropdown-loading">
                  <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> Cargando…
                </div>
              </div>
            </li>
            <li role="none"><a href="{{ route('home') }}#novedades"      class="nav-link" role="menuitem">Novedades</a></li>
            <li role="none"><a href="{{ route('home') }}#publicaciones"  class="nav-link" role="menuitem">Nuestras publicaciones</a></li>
            @if($navigationPages->isNotEmpty())
              <li class="nav-item-dropdown pages" role="none">
                <button class="nav-link dropdown-trigger" aria-haspopup="true" aria-expanded="false" role="menuitem">
                  <span class="nav-link-text">Más páginas</span>
                  <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu" role="menu" aria-label="Páginas institucionales">
                  @foreach($navigationPages as $navigationPage)
                    <a class="dropdown-item" role="menuitem" href="{{ route('pages.show', $navigationPage->slug) }}">
                      {{ $navigationPage->title }}
                    </a>
                  @endforeach
                </div>
              </li>
            @endif
            @if(auth()->check())
              <li class="nav-item-dropdown usuario" role="none">
                <button class="nav-link dropdown-trigger" aria-haspopup="true" aria-expanded="false" role="menuitem">
                  <i class="fas fa-user" aria-hidden="true"></i>
                  <span class="nav-link-text">{{ auth()->user()->name }}</span>
                  <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu" role="menu" aria-label="Usuario">
                  <div class="dropdown-item" role="menuitem">
                    @php $role = auth()->user()->role ?? null; @endphp
                    @if($role === 'admin')
                      <a href="{{ url('/admin') }}">Mi panel</a>
                    @elseif($role === 'director')
                      <a href="{{ route('director.dashboard') }}">Mi panel</a>
                    @elseif($role === 'docente')
                      <a href="{{ url('/teacher') }}">Mi panel</a>
                    @elseif($role === 'alumno')
                      <a href="{{ url('/student') }}">Mi panel</a>
                    @else
                      <a href="{{ url('/') }}">Mi panel</a>
                    @endif
                  </div>
                  <div class="dropdown-item" role="menuitem">
                    <form method="POST" action="{{ url('/logout') }}">@csrf
                      <button type="submit" style="background:none;border:none;padding:0;color:inherit;">Cerrar sesión</button>
                    </form>
                  </div>
                </div>
              </li>
            @else
              <li role="none">
                {{-- Use URL fallback to avoid RouteNotFoundException if auth routes are not defined --}}
                <a href="{{ url('/login') }}" class="nav-link" role="menuitem" aria-label="Iniciar sesión">
                  <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                  <span class="nav-link-text">Iniciar sesión</span>
                </a>
              </li>
            @endif
          @endif
        </ul>

      </div>
    </nav>
  </header>

  <main>
    @if(View::hasSection('full_width'))
      @yield('content')
    @else
      <x-bento-grid-elevado class="container mx-auto my-6">@yield('content')</x-bento-grid-elevado>
    @endif
  </main>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">

        <div class="footer-brand">
          <div class="footer-logo">
            <img src="{{ $footerSettings['media']['logo'] }}" alt="Logo {{ $footerSettings['short_name'] }}" style="height:48px;width:48px;object-fit:contain;" />
            <span>{{ $footerSettings['short_name'] }}</span>
          </div>
          @if($footerSettings['motto'] !== '')
            <p>{{ $footerSettings['motto'] }}</p>
          @endif
          @if($footerSettings['director'] !== '')
            <p style="font-size:.8rem;margin-top:.5rem;color:rgba(255,255,255,.6);">
              <i class="fas fa-user-tie" aria-hidden="true"></i> {{ $footerSettings['director_label'] }}:
              <strong style="color:rgba(255,255,255,.85);">{{ $footerSettings['director'] }}</strong>
            </p>
          @endif
          <div class="social-links" aria-label="Redes sociales">
            @foreach(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'twitter' => 'Twitter/X'] as $network => $label)
              @if($footerSettings['social_'.$network] !== '')
                <a href="{{ $footerSettings['social_'.$network] }}" aria-label="{{ $label }}" rel="noopener noreferrer" target="_blank">
                  <i class="fab fa-{{ $network === 'twitter' ? 'twitter' : $network }}" aria-hidden="true"></i>
                </a>
              @endif
            @endforeach
          </div>
        </div>

        <nav class="footer-nav" aria-label="Navegación del pie de página">
          <h4>{{ $footerSettings['navigation_title'] }}</h4>
          <ul>
            @foreach(range(1, 5) as $navigationIndex)
              @php
                $navigationUrl = $footerSettings['nav_'.$navigationIndex.'_url'];
                if (str_starts_with($navigationUrl, '#')) {
                  $navigationUrl = route('home').$navigationUrl;
                }
              @endphp
              @if($footerSettings['nav_'.$navigationIndex.'_label'] !== '' && $navigationUrl !== '')
                <li><a href="{{ $navigationUrl }}">{{ $footerSettings['nav_'.$navigationIndex.'_label'] }}</a></li>
              @endif
            @endforeach
          </ul>
        </nav>

        <address class="footer-contact">
          <h4>{{ $footerSettings['contact_title'] }}</h4>
          @if($footerSettings['address'] !== '')
            <div class="contact-item">
              <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
              <span>{{ $footerSettings['address'] }}</span>
            </div>
          @endif
          @if($footerSettings['phone'] !== '')
            <div class="contact-item">
              <i class="fas fa-phone" aria-hidden="true"></i>
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $footerSettings['phone']) }}">{{ $footerSettings['phone'] }}</a>
            </div>
          @endif
          @if($footerSettings['email'] !== '')
            <div class="contact-item">
              <i class="fas fa-envelope" aria-hidden="true"></i>
              <a href="mailto:{{ $footerSettings['email'] }}">{{ $footerSettings['email'] }}</a>
            </div>
          @endif
        </address>

      </div>

      <div class="footer-bottom">
        <p>&copy; {{ $footerSettings['year'] }} {{ $footerSettings['institution_name'] }}. {{ $footerSettings['copyright'] }}</p>
        <p>{{ $footerSettings['credit'] }}</p>
      </div>
    </div>
  </footer>

  <!-- BACK TO TOP -->
  <button id="backToTop" class="back-to-top" aria-label="Volver arriba">
    <i class="fas fa-arrow-up"></i>
  </button>

  <!-- File viewer / Drive integration removed — site uses local previews only -->

  <!-- Scripts -->
  <?php
    $school = config('school');
    $schoolConfig = [
      'demoMode' => $school['demo_mode'],
      'apiRoutes' => [
        'proyecto' => route('drive.proyecto'),
        'subfolder' => route('drive.subfolder'),
        'localProject' => route('drive.local_project'),
        'novedades' => route('drive.novedades'),
        'publicaciones' => route('drive.publicaciones'),
      ],
      'stats' => [
        'students' => $school['stats']['students'],
        'teachers' => $school['stats']['teachers'],
        'years' => $school['stats']['years'],
        'projects' => $school['stats']['projects'],
      ],
    ];
  ?>
  <script>
    // Configuración de la institución para el frontend
    window.SCHOOL_CONFIG = <?php echo json_encode($schoolConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
  </script>
  <script>
    /* Fallback: ocultar el loader si por alguna razón el evento 'load' tarda
       o los scripts principales no se ejecutan. Esto permite interacción
       inmediata (mobile menu, dropdowns) mientras se investigan errores JS. */
    (function(){
      const loader = document.getElementById('loader');
      if (!loader) return;
      const hide = () => {
        loader.classList.add('hidden');
        try { loader.addEventListener('transitionend', () => loader.remove(), { once: true }); } catch(e){ /* noop */ }
      };

      if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(hide, 500);
      } else {
        document.addEventListener('DOMContentLoaded', () => setTimeout(hide, 500), { once: true });
      }

      // Safety net: hide after 6s in any case so overlay no quede permanente
      setTimeout(() => { if (document.getElementById('loader')) hide(); }, 6000);
    })();
  </script>
  <script src="{{ asset('js/drive.js') }}"></script>
  <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>

  <script>
    // Confirmation for destructive actions
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('form.confirm-delete').forEach(f => {
        f.addEventListener('submit', function(e){
          if (!confirm('¿Estás seguro que quieres eliminar este elemento? Esta acción no se puede deshacer.')) {
            e.preventDefault();
          }
        });
      });
    });
  </script>

  @stack('scripts')

  <?php
    // Allow database-backed settings to override config('school') when available
    if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
        $s = \App\Models\Setting::class;
        try {
            $school['name'] = \App\Models\Setting::get('site_name', $school['name'] ?? '');
            $school['short_name'] = \App\Models\Setting::get('site_short_name', $school['short_name'] ?? '');
            $school['motto'] = \App\Models\Setting::get('site_motto', $school['motto'] ?? '');
            $school['year'] = \App\Models\Setting::get('site_year', $school['year'] ?? date('Y'));
            $school['director'] = \App\Models\Setting::get('site_director', $school['director'] ?? '');
            $school['colors']['primary'] = \App\Models\Setting::get('color_primary', $school['colors']['primary'] ?? '');
            $school['colors']['secondary'] = \App\Models\Setting::get('color_secondary', $school['colors']['secondary'] ?? '');
            $school['social']['facebook'] = \App\Models\Setting::get('social_facebook', $school['social']['facebook'] ?? '');
            $school['social']['instagram'] = \App\Models\Setting::get('social_instagram', $school['social']['instagram'] ?? '');
            $school['social']['youtube'] = \App\Models\Setting::get('social_youtube', $school['social']['youtube'] ?? '');
            $school['social']['twitter'] = \App\Models\Setting::get('social_twitter', $school['social']['twitter'] ?? '');
        } catch (\Throwable $e) {
            // ignore if something goes wrong reading settings
        }
    }
  ?>
</body>
</html>
