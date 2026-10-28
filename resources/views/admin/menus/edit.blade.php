@extends('layouts.admin')

@section('admin_content')
  <div class="admin-card">
    <div class="section-header admin-page-header">
      <h2 class="section-title">Editar menú</h2>
      <div class="section-divider"></div>
    </div>

    <div class="admin-panel">
      @if(session('success'))<div class="admin-alert-success">{{ session('success') }}</div>@endif
      <form method="POST" action="{{ route('admin.menus.update', $menu->id) }}" class="admin-form-stack">
        @csrf @method('PUT')
        <label>Título</label>
        <input type="text" name="title" value="{{ $menu->title }}" required />
        <label>Slug</label>
        <input type="text" name="slug" value="{{ $menu->slug }}" required />

        <h4 style="margin-top:1rem;">Items del menú</h4>
        <div id="items">
          @foreach($menu->items as $i => $item)
            <div class="menu-item-row">
              <input type="text" name="item_title[]" value="{{ $item->title }}" placeholder="Título" />
              <input type="text" name="item_url[]" value="{{ $item->url }}" placeholder="URL" />
              <input type="number" name="item_position[]" value="{{ $item->position }}" style="width:80px;" />
            </div>
          @endforeach
        </div>
        <button type="button" id="addItem" class="btn btn-sm btn-outline-dark">Agregar item</button>

        <div class="admin-actions-row"><button class="btn btn-primary" type="submit">Guardar menú</button></div>
      </form>
    </div>
  </div>

@push('scripts')
  <script>
    document.getElementById('addItem').addEventListener('click', function(){
      const container = document.getElementById('items');
      const div = document.createElement('div');
      div.className = 'menu-item-row';
      div.innerHTML = '<input type="text" name="item_title[]" placeholder="Título" /> <input type="text" name="item_url[]" placeholder="URL" /> <input type="number" name="item_position[]" value="0" style="width:80px;" />';
      container.appendChild(div);
    });
  </script>
@endpush

@endsection
