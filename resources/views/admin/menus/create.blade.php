@extends('layouts.admin')

@section('admin_content')
  <div class="admin-card">
    <div class="section-header admin-page-header">
      <h2 class="section-title">Crear menú</h2>
      <div class="section-divider"></div>
    </div>

    <div class="admin-panel">
      <form method="POST" action="{{ route('admin.menus.store') }}" class="admin-form-stack">
        @csrf
        <label>Título</label>
        <input type="text" name="title" required />
        <label>Slug (ej: main)</label>
        <input type="text" name="slug" required />
        <div class="admin-actions-row"><button class="btn btn-primary" type="submit">Crear menú</button></div>
      </form>
    </div>
  </div>
@endsection
