@extends('layouts.admin')

@php($adminSection = 'dashboard')

@section('admin_content')
  <div class="admin-card admin-dashboard">
    <header class="dashboard-heading">
      <div>
        <span class="dashboard-eyebrow"><i class="fas fa-layer-group" aria-hidden="true"></i> Centro de gestión</span>
        <h1 class="dashboard-title">Panel de administración</h1>
        <p class="dashboard-subtitle">Todo lo necesario para gestionar tu institución, en un solo lugar.</p>
      </div>
      <div class="dashboard-heading-actions" aria-label="Preferencias visuales">
        <button id="themeToggle" class="dashboard-tool-button" type="button" title="Alternar contraste">
          <i class="fas fa-circle-half-stroke" aria-hidden="true"></i><span>Contraste</span>
        </button>
        <button id="futuristicToggle" class="dashboard-tool-button" type="button" title="Activar o desactivar el modo futurista">
          <i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i><span>Modo futurista</span>
        </button>
      </div>
    </header>

    <section class="dashboard-welcome" aria-labelledby="dashboardWelcomeTitle">
      <div class="dashboard-welcome-copy">
        <span class="dashboard-welcome-kicker"><i class="fas fa-sun" aria-hidden="true"></i> Tu espacio de trabajo</span>
        <h2 id="dashboardWelcomeTitle">Hola, {{ $user->name }}</h2>
        <p>Administra usuarios, organiza cursos y mantén los recursos institucionales al día.</p>
      </div>
      <div class="dashboard-welcome-actions">
        <a class="dashboard-primary-action" href="{{ route('admin.users.create') }}">
          <i class="fas fa-user-plus" aria-hidden="true"></i><span>Crear usuario</span>
          <i class="fas fa-arrow-right dashboard-action-arrow" aria-hidden="true"></i>
        </a>
        <a class="dashboard-secondary-action" href="{{ route('admin.courses.create') }}">
          <i class="fas fa-book-open" aria-hidden="true"></i><span>Nuevo curso</span>
        </a>
      </div>
      <div class="dashboard-date">
        <span><i class="far fa-calendar" aria-hidden="true"></i> Hoy</span>
        <strong>{{ now()->format('d/m/Y') }}</strong>
      </div>
    </section>

    <section class="dashboard-metrics" aria-label="Resumen de la institución">
      <a href="{{ route('admin.users.index') }}" class="dashboard-metric dashboard-metric-users">
        <span class="dashboard-metric-icon"><i class="fas fa-users" aria-hidden="true"></i></span>
        <span class="dashboard-metric-copy">
          <span class="dashboard-metric-label">Usuarios registrados</span>
          <strong class="dashboard-metric-value count" data-count="{{ $stats['users'] }}">{{ $stats['users'] }}</strong>
          <span class="dashboard-metric-note">Cuentas institucionales</span>
        </span>
        <i class="fas fa-arrow-up-right-from-square dashboard-metric-link" aria-hidden="true"></i>
      </a>

      <a href="{{ route('admin.files.panel') }}" class="dashboard-metric dashboard-metric-files">
        <span class="dashboard-metric-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></span>
        <span class="dashboard-metric-copy">
          <span class="dashboard-metric-label">Recursos y archivos</span>
          <strong class="dashboard-metric-value count" data-count="{{ $stats['files'] }}">{{ $stats['files'] }}</strong>
          <span class="dashboard-metric-note">Material disponible</span>
        </span>
        <i class="fas fa-arrow-up-right-from-square dashboard-metric-link" aria-hidden="true"></i>
      </a>

      <a href="{{ route('admin.files.panel') }}" class="dashboard-metric dashboard-metric-published">
        <span class="dashboard-metric-icon"><i class="fas fa-bullhorn" aria-hidden="true"></i></span>
        <span class="dashboard-metric-copy">
          <span class="dashboard-metric-label">Recursos publicados</span>
          <strong class="dashboard-metric-value count" data-count="{{ $stats['published'] }}">{{ $stats['published'] }}</strong>
          <span class="dashboard-metric-note">Visibles en el sitio</span>
        </span>
        <i class="fas fa-arrow-up-right-from-square dashboard-metric-link" aria-hidden="true"></i>
      </a>
    </section>

    <section class="dashboard-workspace" aria-label="Actividad y accesos de gestión">
      <article class="dashboard-panel dashboard-activity-panel">
        <header class="dashboard-panel-heading">
          <div>
            <span class="dashboard-panel-kicker">Seguimiento</span>
            <h2>Actividad reciente</h2>
            <p>Las últimas actualizaciones de la plataforma.</p>
          </div>
          <button id="realtimeToggle" class="dashboard-live-button" type="button" aria-pressed="false">
            <span class="dashboard-live-indicator" aria-hidden="true"></span>
            <span>Activar en vivo</span>
          </button>
        </header>
        <ul id="recentList" class="dashboard-activity-list" aria-live="polite"></ul>
        <div id="recentEmptyState" class="dashboard-empty-state">
          <span class="dashboard-empty-icon"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i></span>
          <div>
            <strong>Aún no hay actividad registrada</strong>
            <p>Cuando se creen usuarios, cursos o archivos, aparecerán aquí.</p>
          </div>
        </div>
      </article>

      <article class="dashboard-panel dashboard-shortcuts-panel">
        <header class="dashboard-panel-heading">
          <div>
            <span class="dashboard-panel-kicker">Accesos directos</span>
            <h2>Gestión diaria</h2>
            <p>Entra rápidamente a las tareas más frecuentes.</p>
          </div>
        </header>
        <nav class="dashboard-shortcuts" aria-label="Accesos rápidos de administración">
          <a href="{{ route('admin.users.index') }}" class="dashboard-shortcut">
            <span class="dashboard-shortcut-icon"><i class="fas fa-users-cog" aria-hidden="true"></i></span>
            <span><strong>Usuarios</strong><small>Administrar cuentas</small></span>
            <i class="fas fa-chevron-right dashboard-shortcut-arrow" aria-hidden="true"></i>
          </a>
          <a href="{{ route('admin.courses.index') }}" class="dashboard-shortcut">
            <span class="dashboard-shortcut-icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></span>
            <span><strong>Cursos</strong><small>Organizar la enseñanza</small></span>
            <i class="fas fa-chevron-right dashboard-shortcut-arrow" aria-hidden="true"></i>
          </a>
          <a href="{{ route('admin.assignments.create') }}" class="dashboard-shortcut">
            <span class="dashboard-shortcut-icon"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i></span>
            <span><strong>Asignaciones</strong><small>Vincular cursos y docentes</small></span>
            <i class="fas fa-chevron-right dashboard-shortcut-arrow" aria-hidden="true"></i>
          </a>
          <a href="{{ route('admin.files.panel') }}" class="dashboard-shortcut">
            <span class="dashboard-shortcut-icon"><i class="fas fa-cloud-arrow-up" aria-hidden="true"></i></span>
            <span><strong>Publicaciones</strong><small>Gestionar recursos del sitio</small></span>
            <i class="fas fa-chevron-right dashboard-shortcut-arrow" aria-hidden="true"></i>
          </a>
        </nav>
      </article>
    </section>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    // Counter animation for KPI numbers
    document.querySelectorAll('.count').forEach(el => {
      const target = parseInt(el.getAttribute('data-count') || '0', 10);
      if (!target) {
        el.textContent = '0';
        return;
      }
      let cur = 0; const step = Math.max(1, Math.floor(target / 60));
      const id = setInterval(()=>{
        cur += step; if (cur >= target) { cur = target; clearInterval(id); }
        el.textContent = cur.toLocaleString();
      }, 12);
    });

    // Realtime activity polling (simple polling fallback for websockets)
    (function(){
      const listEl = document.getElementById('recentList');
      const emptyState = document.getElementById('recentEmptyState');
      const toggle = document.getElementById('realtimeToggle');
      let intervalId = null;
      const fetchAndRender = () => {
        fetch('{{ route('admin.activity.recent_json') }}', { credentials: 'same-origin' })
          .then(r=>r.json())
          .then(json=>{
            const data = json.data || [];
            if (!listEl) return;
            if (emptyState) emptyState.hidden = data.length > 0;
            listEl.innerHTML = data.map(item=>{
              const time = new Date(item.created_at).toLocaleString();
              const avatar = (item.actor_name||'A').charAt(0).toUpperCase();
              const editAction = item.type === 'archivo' && Number.isInteger(Number(item.file_id)) && Number(item.file_id) > 0
                ? `<a class="dashboard-activity-edit" href="{{ route('admin.files.panel') }}?edit=${encodeURIComponent(item.file_id)}" aria-label="Editar archivo">Editar</a>`
                : '';
              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
              const deleteAction = editAction && item.delete_url
                ? `<form class="dashboard-activity-delete" action="${item.delete_url}" method="POST">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="dashboard-activity-delete-button" type="submit" aria-label="Eliminar archivo">Eliminar</button>
                  </form>`
                : '';
              return `<li class="bento-card" style="display:flex;align-items:center;gap:.75rem;">
                <div style="width:44px;height:44px;border-radius:10px;display:grid;place-items:center;background:linear-gradient(135deg,var(--clr-gold),var(--clr-navy));color:#fff;font-weight:700;">${avatar}</div>
                <div style="flex:1;">
                  <div style="font-weight:700;">${item.title}</div>
                  <div class="muted-note" style="font-size:.9rem;">${item.subtitle || ''} — <small>${time}</small></div>
                </div>
                <div class="muted-note" style="font-size:.85rem;">${item.type}</div>
                <div class="dashboard-activity-actions">${editAction}${deleteAction}</div>
              </li>`;
            }).join('');
          }).catch(()=>{});
      };

      listEl?.addEventListener('submit', event => {
        if (event.target.matches('.dashboard-activity-delete')
          && !confirm('¿Estás seguro que quieres eliminar este elemento? Esta acción no se puede deshacer.')) {
          event.preventDefault();
        }
      });

      function startPolling(){
        fetchAndRender();
        intervalId = setInterval(fetchAndRender, 8000);
        if (toggle) {
          toggle.setAttribute('aria-pressed', 'true');
          toggle.querySelector('span:last-child').textContent = 'En vivo';
        }
      }
      function stopPolling(){
        if (intervalId) clearInterval(intervalId);
        intervalId = null;
        if (toggle) {
          toggle.setAttribute('aria-pressed', 'false');
          toggle.querySelector('span:last-child').textContent = 'Activar en vivo';
        }
      }

      if (toggle){
        toggle.addEventListener('click', function(){
          const enabled = !!localStorage.getItem('adminRealtime');
          if (enabled){
            localStorage.removeItem('adminRealtime'); stopPolling();
          } else { localStorage.setItem('adminRealtime','1'); startPolling(); }
        });
      }

      // Auto start if user preference set
      if (localStorage.getItem('adminRealtime')) startPolling();
      else fetchAndRender();
    })();

    // Theme toggle: high contrast and futuristic modes
    (function(){
      const contrastBtn = document.getElementById('themeToggle');
      const futurBtn = document.getElementById('futuristicToggle');
      const contrastKey = 'adminHighContrast';
      const futurKey = 'adminFuturistic';

      function setContrast(on){
        document.documentElement.classList.toggle('admin-high-contrast', !!on);
        if (on) localStorage.setItem(contrastKey,'1'); else localStorage.removeItem(contrastKey);
      }
      function setFuturistic(on){
        document.documentElement.classList.toggle('admin-futuristic', !!on);
        if (on) {
          localStorage.setItem(futurKey,'1');
          // disable high contrast when futuristic is enabled
          setContrast(false);
        } else {
          localStorage.removeItem(futurKey);
        }
      }

      if (contrastBtn){
        contrastBtn.addEventListener('click', function(){
          const isFutur = document.documentElement.classList.contains('admin-futuristic');
          if (isFutur) { setFuturistic(false); }
          setContrast(!document.documentElement.classList.contains('admin-high-contrast'));
        });
      }

      if (futurBtn){
        futurBtn.addEventListener('click', function(){
          setFuturistic(!document.documentElement.classList.contains('admin-futuristic'));
        });
      }

      if (localStorage.getItem(futurKey)) setFuturistic(true);
      else if (localStorage.getItem(contrastKey)) setContrast(true);
    })();
  });
</script>
@endpush
