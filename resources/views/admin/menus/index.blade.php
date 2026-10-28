@extends('layouts.admin')

@section('admin_content')
  <div class="admin-card">
    <div class="section-header admin-page-header">
      <h2 class="section-title">Menús</h2>
      <div class="section-divider"></div>
      <p>Gestiona las barras de navegación del sitio.</p>
    </div>

    <div class="admin-panel">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <h3 class="admin-panel-title">Listado de menús</h3>
        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">Crear menú</a>
      </div>

      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead><tr><th>Título</th><th>Slug</th><th>Items</th><th></th></tr></thead>
          <tbody>
            @foreach($menus as $m)
              <tr>
                <td>{{ $m->title }}</td>
                <td>{{ $m->slug }}</td>
                <td>{{ $m->items->count() }}</td>
                <td>
                  <a href="{{ route('admin.menus.edit', $m->id) }}" class="btn btn-sm btn-outline-dark">Editar</a>
                  <form method="POST" action="{{ route('admin.menus.destroy', $m->id) }}" style="display:inline-block;" class="admin-inline-form confirm-delete">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-dark" type="submit">Eliminar</button></form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
