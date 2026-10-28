@extends('layouts.admin')

@php($adminSection = 'users-create')

@section('admin_content')
<div class="admin-card admin-users" style="max-width:700px;">
  <div class="section-header admin-page-header" style="text-align:left;">
    <h2 class="section-title">Crear usuario</h2>
    <div class="section-divider"></div>
  </div>

  @if($errors->any())
    <div class="alert alert-error">
      <ul>
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.users.store') }}" method="POST">
    @csrf
    <div class="admin-form-stack">
      <label>Nombre
        <div style="display:flex;gap:.75rem;align-items:center;">
          <div class="user-avatar-preview" id="user-avatar-preview">{{ strtoupper(substr(old('name',''),0,1)) }}</div>
          <input id="user-name-input" type="text" name="name" value="{{ old('name') }}" required />
        </div>
      </label>
      <label>Email
        <input type="email" name="email" value="{{ old('email') }}" required />
      </label>
      <label>Contraseña
        <input type="password" name="password" required />
      </label>
      <label>Rol
        <select name="role" required>
          <option value="docente" {{ old('role')=='docente'?'selected':'' }}>Docente</option>
          <option value="director" {{ old('role')=='director'?'selected':'' }}>Director</option>
          <option value="alumno" {{ old('role')=='alumno'?'selected':'' }}>Alumno</option>
          <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
        </select>
      </label>

      <div class="admin-actions-row admin-actions-end">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-dark">Cancelar</a>
        <button class="btn btn-primary" type="submit">Crear</button>
      </div>
    </div>
  </form>
</div>
@endsection

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      const nameInput = document.getElementById('user-name-input');
      const avatar = document.getElementById('user-avatar-preview');
      if(!nameInput || !avatar) return;
      const defaultInitial = {!! json_encode(strtoupper(substr(old('name',''),0,1) ?: '')) !!};
      function updateAvatar(){
        const v = (nameInput.value||'').trim();
        avatar.textContent = v ? v[0].toUpperCase() : defaultInitial;
      }
      nameInput.addEventListener('input', updateAvatar);
      updateAvatar();
    });
    </script>
    @endpush
