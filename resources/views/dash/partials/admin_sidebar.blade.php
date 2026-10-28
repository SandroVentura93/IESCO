@php($isDirector = auth()->user()?->role === 'director')

<aside id="adminSidebar" class="admin-sidebar" aria-label="{{ $isDirector ? 'Menú de dirección' : 'Menú de administración' }}">
  <div class="admin-sidebar-header">
    <div class="admin-sidebar-avatar">
      <img src="{{ asset('images/LOGO.png') }}" alt="Logo {{ $school['short_name'] ?? 'IE' }}" />
    </div>
    <div class="meta">
      <span class="admin-sidebar-kicker">{{ $isDirector ? 'Panel institucional' : 'Panel interno' }}</span>
      <h3>{{ $isDirector ? 'Dirección' : 'Administración' }}</h3>
      <p>{{ $isDirector ? 'Gestión y seguimiento académico.' : 'Accesos rápidos para gestionar la institución.' }}</p>
    </div>
    <button id="sidebarToggle" class="sidebar-toggle" type="button" aria-label="Colapsar menú" aria-expanded="true" aria-controls="adminSidebarNav" title="Colapsar menú">
      <i class="fas fa-angle-left"></i>
    </button>
  </div>

  <div class="admin-sidebar-search" role="search">
    <label class="admin-sidebar-search-label" for="adminSidebarSearch">Buscar en el menú</label>
    <div class="admin-sidebar-search-control">
      <i class="fas fa-search" aria-hidden="true"></i>
      <input id="adminSidebarSearch" type="search" class="sidebar-search-input" placeholder="Buscar opción..." aria-label="Buscar opción del menú" aria-controls="adminSidebarNav" />
      <button class="admin-sidebar-search-clear" type="button" aria-label="Limpiar búsqueda" hidden><i class="fas fa-times" aria-hidden="true"></i></button>
    </div>
    <p class="admin-sidebar-search-empty" role="status" hidden>No hay opciones que coincidan.</p>
  </div>

  <nav id="adminSidebarNav" class="admin-sidebar-nav" aria-label="{{ $isDirector ? 'Navegación de dirección' : 'Navegación de administración' }}">
    <div class="admin-sidebar-group" data-sidebar-group>
      <h4>General</h4>
      <a href="{{ $isDirector ? route('director.dashboard') : route('admin.dashboard') }}" class="{{ in_array(($adminSection ?? ''), ['dashboard', 'director'], true) ? 'active' : '' }}" title="{{ $isDirector ? 'Panel de dirección' : 'Resumen del sistema' }}" aria-label="{{ $isDirector ? 'Panel de dirección' : 'Resumen del sistema' }}" @if(in_array(($adminSection ?? ''), ['dashboard', 'director'], true)) aria-current="page" @endif>
        <i class="fas fa-chart-line" aria-hidden="true"></i>
        <span>{{ $isDirector ? 'Panel de dirección' : 'Resumen del sistema' }}</span>
      </a>
    </div>

    <div class="admin-sidebar-group" data-sidebar-group>
      <h4>{{ $isDirector ? 'Gestión académica' : 'Gestión' }}</h4>
      @unless($isDirector)
        <a href="{{ route('admin.files.panel') }}" class="{{ ($adminSection ?? '') === 'files-panel' ? 'active' : '' }}" title="Archivos y publicaciones" aria-label="Archivos y publicaciones" @if(($adminSection ?? '') === 'files-panel') aria-current="page" @endif>
          <i class="fas fa-folder-open" aria-hidden="true"></i>
          <span>Archivos y publicaciones</span>
        </a>
        <a href="{{ route('admin.users.index') }}" class="{{ in_array(($adminSection ?? ''), ['users', 'users-create'], true) ? 'active' : '' }}" title="Gestión de usuarios" aria-label="Gestión de usuarios" @if(in_array(($adminSection ?? ''), ['users', 'users-create'], true)) aria-current="page" @endif>
          <i class="fas fa-users-cog" aria-hidden="true"></i>
          <span>Gestión de usuarios</span>
        </a>
      @endunless
      <a href="{{ route('admin.courses.index') }}" class="{{ in_array(($adminSection ?? ''), ['courses', 'courses-create'], true) ? 'active' : '' }}" title="Gestión de cursos" aria-label="Gestión de cursos" @if(in_array(($adminSection ?? ''), ['courses', 'courses-create'], true)) aria-current="page" @endif>
        <i class="fas fa-school" aria-hidden="true"></i>
        <span>Gestión de cursos</span>
      </a>
      <a href="{{ route('admin.assignments.create') }}" class="{{ ($adminSection ?? '') === 'assignments' ? 'active' : '' }}" title="Asignar curso a docente" aria-label="Asignar curso a docente" @if(($adminSection ?? '') === 'assignments') aria-current="page" @endif>
        <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i>
        <span>Asignar curso a docente</span>
      </a>
      <a href="{{ route('admin.students.index') }}" class="{{ ($adminSection ?? '') === 'students' ? 'active' : '' }}" title="Gestión de alumnos" aria-label="Gestión de alumnos" @if(($adminSection ?? '') === 'students') aria-current="page" @endif>
        <i class="fas fa-user-graduate" aria-hidden="true"></i>
        <span>Gestión de alumnos</span>
      </a>
      <a href="{{ route('admin.enrollments.index') }}" class="{{ ($adminSection ?? '') === 'enrollments' ? 'active' : '' }}" title="Matrículas" aria-label="Matrículas" @if(($adminSection ?? '') === 'enrollments') aria-current="page" @endif>
        <i class="fas fa-clipboard-check" aria-hidden="true"></i>
        <span>Matrículas</span>
      </a>
      <a href="{{ route('director.resources.index') }}" class="{{ ($adminSection ?? '') === 'director-resources' ? 'active' : '' }}" title="Recursos pedagógicos" aria-label="Recursos pedagógicos" @if(($adminSection ?? '') === 'director-resources') aria-current="page" @endif>
        <i class="fas fa-book-open" aria-hidden="true"></i>
        <span>Recursos pedagógicos</span>
      </a>
    </div>

    @unless($isDirector)
      <div class="admin-sidebar-group" data-sidebar-group>
        <h4>Contenido</h4>
        <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" title="Gestión de páginas" aria-label="Gestión de páginas" @if(request()->routeIs('admin.pages.*')) aria-current="page" @endif>
          <i class="fas fa-file-alt" aria-hidden="true"></i>
          <span>Gestión de páginas</span>
        </a>
        <a href="{{ route('admin.menus.index') }}" class="{{ request()->routeIs('admin.menus.*') ? 'active' : '' }}" title="Gestión de menús" aria-label="Gestión de menús" @if(request()->routeIs('admin.menus.*')) aria-current="page" @endif>
          <i class="fas fa-list" aria-hidden="true"></i>
          <span>Gestión de menús</span>
        </a>
      </div>

      <div class="admin-sidebar-group" data-sidebar-group>
        <h4>Sistema</h4>
        <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" title="Configuración del sitio" aria-label="Configuración del sitio" @if(request()->routeIs('admin.settings.*')) aria-current="page" @endif>
          <i class="fas fa-cogs" aria-hidden="true"></i>
          <span>Configuración del sitio</span>
        </a>
      </div>
    @endunless
  </nav>

  <div class="admin-sidebar-footer">
    <div class="sidebar-user">
      <img src="{{ asset('images/LOGO.png') }}" alt="avatar" class="sidebar-user-avatar" />
      <div class="sidebar-user-info">
        <div class="sidebar-user-name">{{ auth()->user()->name ?? 'Administrador' }}</div>
        <div class="sidebar-user-role muted-note">{{ \App\Models\User::ROLES[auth()->user()->role] ?? 'Sesión activa' }}</div>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-outline-dark btn-sm"><i class="fas fa-sign-out-alt" aria-hidden="true"></i><span>Cerrar sesión</span></button>
    </form>
  </div>
</aside>