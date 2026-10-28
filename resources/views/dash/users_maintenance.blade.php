@extends('layouts.admin')

@php($adminSection = 'users-maintenance')

@section('admin_content')
  <div class="admin-card">
    <div class="section-header admin-page-header" style="text-align:left;">
      <h2 class="section-title">Mantenimiento de usuarios</h2>
      <div class="section-divider"></div>
      <p>Gestiona creación rápida de usuarios, asignación de cursos y revisión de cuentas.</p>
    </div>

    <div class="admin-split-grid">
      <div class="admin-panel">
        <h4 class="admin-panel-title">Crear usuario</h4>
        <form method="POST" action="{{ route('admin.users.store') }}" class="admin-form-stack">
          @csrf
          <label>Nombre</label>
          <input name="name" required />
          <label>Correo</label>
          <input name="email" type="email" required />
          <label>Contraseña</label>
          <input name="password" type="password" required />
          <label>Rol</label>
          <select name="role">
            <option value="admin">Administrador</option>
            <option value="docente">Docente</option>
            <option value="alumno">Alumno</option>
          </select>
          <div class="admin-actions-row">
            <button class="btn btn-primary" type="submit">Crear usuario</button>
          </div>
        </form>

        <hr class="admin-divider" />
        <h4 class="admin-panel-title">Asignar curso a docente</h4>
        <form method="POST" action="{{ route('teacher.assign.course') }}" class="admin-form-stack">
          @csrf
          <label>Docente</label>
          <select name="teacher_id" required>
            <option value="">Selecciona un docente...</option>
            @foreach(\App\Models\User::where('role','docente')->get() as $doc)
              <option value="{{ $doc->id }}">{{ $doc->name }} — {{ $doc->email }}</option>
            @endforeach
          </select>
          <label>Curso</label>
            <select name="course_id" required>
            <option value="">Selecciona un curso...</option>
            @foreach(\App\Models\Course::orderBy('name')->get() as $ac)
              <option value="{{ $ac->id }}">{{ $ac->name }}</option>
            @endforeach
          </select>
            <label for="maintenance-assignment-grade">Grado</label>
            <select id="maintenance-assignment-grade" name="grade" required>
              <option value="">Selecciona un grado...</option>
              @foreach(\App\Models\CourseAssignment::GRADES as $grade)
                <option value="{{ $grade }}">{{ $grade }}</option>
              @endforeach
            </select>
            <label for="maintenance-assignment-section">Sección (opcional)</label>
            <input id="maintenance-assignment-section" name="section" type="text" maxlength="60" placeholder="Ej. A">
            <div class="admin-actions-row">
              <button class="btn btn-primary" type="submit">Asignar curso</button>
            </div>
        </form>
      </div>

      <div class="admin-panel admin-panel-wide">
        <h4 class="admin-panel-title">Usuarios</h4>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Creado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $u)
                <tr>
                  <td>{{ $u->name }}</td>
                  <td>{{ $u->email }}</td>
                  <td>{{ $u->role }}</td>
                  <td>{{ $u->created_at->format('Y-m-d') }}</td>
                  <td class="admin-actions-cell">
                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-outline-dark btn-sm">Editar</a>
                    @if(auth()->id() !== $u->id)
                      <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" class="confirm-delete admin-inline-form">@csrf @method('DELETE')<button class="btn btn-outline-dark btn-sm" type="submit">Eliminar</button></form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="admin-pagination">{{ $users->links() }}</div>
      </div>
    </div>
  </div>
@endsection