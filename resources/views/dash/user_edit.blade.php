@extends('layouts.admin')

@php($adminSection = 'users')

@section('admin_content')
    <div class="admin-card" style="max-width:760px;">
      <div class="section-header admin-page-header" style="text-align:left;">
        <h2 class="section-title">Editar usuario</h2>
        <div class="section-divider"></div>
      </div>

      <form method="POST" action="{{ route('admin.users.update', $u->id) }}" class="admin-form-stack">
        @csrf
        @method('PUT')
        <label>Nombre</label>
        <input name="name" value="{{ $u->name }}" required />
        <label>Correo</label>
        <input name="email" type="email" value="{{ $u->email }}" required />
        <label>Contraseña (dejar en blanco para no cambiar)</label>
        <input name="password" type="password" />
        <label>Rol</label>
        <select name="role">
          <option value="admin" {{ $u->role=='admin'?'selected':'' }}>Administrador</option>
          <option value="director" {{ $u->role=='director'?'selected':'' }}>Director</option>
          <option value="docente" {{ $u->role=='docente'?'selected':'' }}>Docente</option>
          <option value="alumno" {{ $u->role=='alumno'?'selected':'' }}>Alumno</option>
        </select>
        <div class="admin-actions-row">
          <button class="btn btn-primary" type="submit">Actualizar usuario</button>
          <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark">Volver</a>
        </div>
      </form>
    </div>
@endsection
