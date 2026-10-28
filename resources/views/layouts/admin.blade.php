@extends('layouts.app')

@section('full_width')
@endsection

@section('content')
  @push('styles')
    <?php $adminSidebarVersion = file_exists(public_path('css/admin-sidebar.css')) ? filemtime(public_path('css/admin-sidebar.css')) : time(); ?>
    <link rel="stylesheet" href="{{ asset('css/admin-sidebar.css') }}?v={{ $adminSidebarVersion }}">
    <?php $adminPremiumVersion = file_exists(public_path('css/admin-premium.css')) ? filemtime(public_path('css/admin-premium.css')) : time(); ?>
    <link rel="stylesheet" href="{{ asset('css/admin-premium.css') }}?v={{ $adminPremiumVersion }}">
    {{-- Futuristic fonts --}}
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
  @endpush

  <section class="section admin-area full-bleed compact {{ ($adminSection ?? '') === 'dashboard' ? 'admin-dashboard-shell' : '' }}">
    {{-- Admin visual overrides consolidated to `public/css/styles.css` --}}

    <div class="admin-layout">
      <button id="adminSidebarMobileToggle" class="admin-sidebar-mobile-trigger" type="button" aria-label="Abrir menú de administración" aria-expanded="false" aria-controls="adminSidebar">
        <i class="fas fa-bars" aria-hidden="true"></i><span>Menú</span>
      </button>
      <button id="adminSidebarBackdrop" class="admin-sidebar-backdrop" type="button" aria-label="Cerrar menú de administración" hidden></button>
      @include('dash.partials.admin_sidebar')

      <div class="admin-content">
        <div class="container">
          <div class="admin-page-header {{ ($adminSection ?? '') === 'dashboard' ? 'admin-layout-header-empty' : '' }}">
            <div>
              @hasSection('admin_title')
                <h2 class="section-title">@yield('admin_title')</h2>
              @endif
              @hasSection('admin_subtitle')
                <p class="muted-note">@yield('admin_subtitle')</p>
              @endif
            </div>
            <div class="admin-toolbar">
              @hasSection('admin_toolbar')
                  @yield('admin_toolbar')
              @endif
            </div>
          </div>

          <div class="admin-body">
            @if(View::hasSection('admin_content'))
              @yield('admin_content')
            @else
              @php
                try {
                  $users = \App\Models\User::count();
                } catch (\Throwable $e) { $users = 0; }
                try {
                  $courses = \App\Models\Course::count();
                } catch (\Throwable $e) { $courses = 0; }
                try {
                  $files = \App\Models\StoredFile::count();
                } catch (\Throwable $e) { $files = 0; }
                $me = auth()->user();
                $initial = $me ? strtoupper(substr($me->name ?? 'A',0,1)) : 'A';
              @endphp

              <div class="admin-hero admin-card">
                <div class="welcome-card">
                  <div class="welcome-avatar">{{ $initial }}</div>
                  <div class="welcome-info">
                    <h3>Hola, {{ $me ? $me->name : 'Administrador' }}</h3>
                    <p>Estado del sistema y accesos rápidos.</p>
                    
                  </div>
                </div>
                <div class="admin-side-panel">
                  <div class="muted-note">Bienvenido, <strong>{{ $me ? $me->name : 'Administrador' }}</strong>. Aquí tienes un resumen rápido.</div>
                </div>
              </div>

              <div class="admin-kpi-grid">
                <div class="admin-kpi-card">
                  <div class="kpi-row">
                    <div class="icon-badge"><i class="fas fa-users" aria-hidden="true"></i></div>
                    <div>
                      <div class="admin-kpi-label">Usuarios</div>
                      <div class="admin-kpi-value">{{ $users }}</div>
                    </div>
                  </div>
                </div>
                <div class="admin-kpi-card">
                  <div class="kpi-row">
                    <div class="icon-badge"><i class="fas fa-file" aria-hidden="true"></i></div>
                    <div>
                      <div class="admin-kpi-label">Archivos</div>
                      <div class="admin-kpi-value">{{ $files }}</div>
                    </div>
                  </div>
                </div>
                <div class="admin-kpi-card">
                  <div class="kpi-row">
                    <div class="icon-badge"><i class="fas fa-graduation-cap" aria-hidden="true"></i></div>
                    <div>
                      <div class="admin-kpi-label">Cursos</div>
                      <div class="admin-kpi-value">{{ $courses }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-split-grid">
                <div>
                  <div class="admin-panel">
                    <h4 class="admin-panel-title">Actividad reciente</h4>
                    <div class="muted-note">No hay actividad reciente registrada.</div>
                  </div>
                </div>
                <div>
                  <div class="admin-side-panel">
                    <h4 class="admin-panel-title">Resumen rápido</h4>
                    <p class="muted-note">Última sincronización: {{ now()->subDays(1)->format('d M Y H:i') }}</p>
                    <div class="admin-divider"></div>
                    
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
@push('scripts')
  <script>
  (function(){
    const key = 'adminSidebarCollapsed';
    const root = document.querySelector('.admin-area');
    const sidebar = document.getElementById('adminSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('adminSidebarMobileToggle');
    const backdrop = document.getElementById('adminSidebarBackdrop');
    const search = document.getElementById('adminSidebarSearch');
    const searchClear = document.querySelector('.admin-sidebar-search-clear');
    const searchEmpty = document.querySelector('.admin-sidebar-search-empty');
    if (!root || !sidebar) return;

    const isMobile = () => window.innerWidth <= 900;
    const updateToggleIcon = collapsed => {
      const icon = toggle?.querySelector('i');
      if (icon) icon.style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
      if (toggle) {
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        toggle.setAttribute('aria-label', collapsed ? 'Expandir menú' : 'Colapsar menú');
        toggle.title = collapsed ? 'Expandir menú' : 'Colapsar menú';
      }
    };
    const setMobileMenu = (open, returnFocus = false) => {
      root.classList.toggle('sidebar-mobile-open', open);
      mobileToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
      mobileToggle?.setAttribute('aria-label', open ? 'Cerrar menú de administración' : 'Abrir menú de administración');
      if (isMobile() && toggle) {
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Menú de administración cerrado');
        toggle.title = open ? 'Cerrar menú' : 'Menú de administración cerrado';
      }
      backdrop?.toggleAttribute('hidden', !open);
      document.body.classList.toggle('admin-sidebar-menu-open', open);
      if (open) {
        (sidebar.querySelector('.admin-sidebar-group a.active:not([hidden])') || sidebar.querySelector('.admin-sidebar-group a:not([hidden])'))?.focus();
      } else if (returnFocus) {
        mobileToggle?.focus();
      }
    };
    const applyViewport = () => {
      if (isMobile()) {
        root.classList.add('sidebar-mobile');
        root.classList.remove('sidebar-collapsed');
        setMobileMenu(false);
      } else {
        setMobileMenu(false);
        root.classList.remove('sidebar-mobile');
        root.classList.toggle('sidebar-collapsed', localStorage.getItem(key) === '1');
        updateToggleIcon(root.classList.contains('sidebar-collapsed'));
      }
    };

    applyViewport();

    toggle?.addEventListener('click', event => {
      event.preventDefault();
      if (isMobile()) {
        setMobileMenu(false, true);
        return;
      }
      const collapsed = root.classList.toggle('sidebar-collapsed');
      localStorage.setItem(key, collapsed ? '1' : '0');
      updateToggleIcon(collapsed);
    });
    mobileToggle?.addEventListener('click', () => setMobileMenu(!root.classList.contains('sidebar-mobile-open')));
    backdrop?.addEventListener('click', () => setMobileMenu(false, true));
    sidebar.querySelectorAll('.admin-sidebar-group a').forEach(link => {
      link.addEventListener('click', () => {
        if (isMobile()) setMobileMenu(false);
      });
    });
    document.addEventListener('keydown', event => {
      if (!root.classList.contains('sidebar-mobile-open')) return;
      if (event.key === 'Escape') {
        setMobileMenu(false, true);
        return;
      }
      if (event.key !== 'Tab') return;

      const focusable = Array.from(sidebar.querySelectorAll('a[href]:not([hidden]), button:not([disabled]), input:not([disabled])'))
        .filter(element => element.getClientRects().length > 0);
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (!first || !last) return;
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });

    search?.addEventListener('input', () => {
      const query = search.value.trim().toLocaleLowerCase();
      let visibleLinks = 0;
      sidebar.querySelectorAll('[data-sidebar-group]').forEach(group => {
        let groupHasMatch = false;
        group.querySelectorAll('a').forEach(link => {
          const matches = !query || `${link.textContent} ${link.getAttribute('aria-label')}`.toLocaleLowerCase().includes(query);
          link.hidden = !matches;
          groupHasMatch = groupHasMatch || matches;
          if (matches) visibleLinks++;
        });
        group.hidden = !groupHasMatch;
      });
      if (searchClear) searchClear.hidden = !query;
      if (searchEmpty) searchEmpty.hidden = visibleLinks > 0;
    });
    searchClear?.addEventListener('click', () => {
      search.value = '';
      search.dispatchEvent(new Event('input', { bubbles: true }));
      search.focus();
    });

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(applyViewport, 150);
    });
  })();
</script>
@endpush
@endsection