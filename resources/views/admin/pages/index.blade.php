@extends('layouts.admin')

@php $adminSection = 'pages'; @endphp

@section('admin_content')
  <div class="admin-card page-manager">
    <header class="page-manager-header">
      <div>
        <span class="page-manager-eyebrow">Contenido del sitio</span>
        <h2 class="section-title">Administración de páginas</h2>
        <p>Organiza el contenido por sección y controla qué páginas aparecen públicamente.</p>
      </div>
      <div class="page-manager-header-actions">
        @if($homepage)
          <a href="{{ route('admin.pages.edit', $homepage) }}" class="btn btn-outline-dark">
            <i class="fas fa-house" aria-hidden="true"></i> Editar portada
          </a>
        @else
          <a href="{{ route('admin.pages.create', ['homepage' => 1]) }}" class="btn btn-outline-dark">
            <i class="fas fa-house" aria-hidden="true"></i> Configurar portada
          </a>
        @endif
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
        <i class="fas fa-plus" aria-hidden="true"></i> Nueva página
        </a>
      </div>
    </header>

    @if(session('success'))
      <div class="admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="site-section-editors" aria-labelledby="site-section-editors-title">
      <div class="site-section-editors-heading">
        <div>
          <span class="page-manager-eyebrow">Portada institucional</span>
          <h3 id="site-section-editors-title">Secciones principales</h3>
          <p>Personaliza los textos e imágenes que aparecen en cada sección pública.</p>
        </div>
      </div>
      <div class="site-section-editor-grid">
        @foreach($sectionEditors as $key => $definition)
          <a class="site-section-editor-card" href="{{ route('admin.pages.sections.edit', $key) }}">
            <span class="site-section-editor-icon"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
            <span><strong>{{ $definition['label'] }}</strong><small>{{ $definition['description'] }}</small></span>
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
          </a>
        @endforeach
      </div>
    </section>

    <div class="page-manager-stats" aria-label="Resumen de páginas">
      <div class="page-manager-stat">
        <span>Total de páginas</span>
        <strong>{{ $totalPages }}</strong>
      </div>
      <div class="page-manager-stat">
        <span>Publicadas</span>
        <strong>{{ $publishedPages }}</strong>
      </div>
      <div class="page-manager-stat">
        <span>Borradores</span>
        <strong>{{ $totalPages - $publishedPages }}</strong>
      </div>
    </div>

    <form method="GET" action="{{ route('admin.pages.index') }}" class="page-manager-filters" role="search">
      <label>
        <span>Sección</span>
        <select name="section" class="form-control">
          <option value="">Todas las secciones</option>
          @foreach($sections as $key => $label)
            <option value="{{ $key }}" {{ $selectedSection === $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </label>
      <label class="page-manager-search">
        <span>Buscar página</span>
        <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Título o dirección" />
      </label>
      <button type="submit" class="btn btn-primary"><i class="fas fa-search" aria-hidden="true"></i> Filtrar</button>
      @if($selectedSection || $search)
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-dark">Limpiar</a>
      @endif
    </form>

    <div class="admin-table-wrap page-manager-table-wrap">
      <table class="admin-table page-manager-table">
        <thead>
          <tr>
            <th>Página</th>
            <th>Sección</th>
            <th>Dirección pública</th>
            <th>Orden</th>
            <th>Estado</th>
            <th class="page-manager-actions-heading">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pages as $page)
            <tr>
              <td>
                <div class="page-manager-title-cell">
                  @if($page->featured_image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->featured_image) }}" alt="" loading="lazy" />
                  @else
                    <span class="page-manager-file-icon" aria-hidden="true"><i class="fas fa-file-lines"></i></span>
                  @endif
                  <div><strong>{{ $page->title }}</strong><small>Actualizada {{ $page->updated_at->format('d/m/Y') }}</small></div>
                </div>
              </td>
              <td><span class="page-manager-section-badge">{{ $page->section_label }}</span></td>
              <td><code>/paginas/{{ $page->slug }}</code></td>
              <td>{{ $page->position }}</td>
              <td>
                <span class="page-manager-status {{ $page->published ? 'is-published' : 'is-draft' }}">
                  <span aria-hidden="true"></span>{{ $page->published ? 'Publicada' : 'Borrador' }}
                </span>
              </td>
              <td>
                <div class="page-manager-actions">
                  @if($page->published)
                    <a href="{{ route('pages.show', $page->slug) }}" class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener noreferrer" aria-label="Ver {{ $page->title }}">
                      <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                  @endif
                  <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-primary">Editar</a>
                  <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="confirm-delete admin-inline-form">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-dark" type="submit" aria-label="Eliminar {{ $page->title }}">
                      <i class="fas fa-trash" aria-hidden="true"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="page-manager-empty">No hay páginas en esta sección. Crea una para empezar a publicar contenido.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="page-manager-pagination">{{ $pages->links() }}</div>
  </div>
@endsection
