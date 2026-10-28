@extends('layouts.admin')

@php($adminSection = 'director')

@section('admin_title', 'Panel de dirección')
@section('admin_subtitle', 'Resumen de la gestión académica de la institución.')

@section('admin_content')
  <section class="admin-kpi-grid" aria-label="Resumen académico">
    <article class="admin-kpi-card">
      <div class="kpi-row">
        <span class="icon-badge"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
        <div><div class="admin-kpi-label">Alumnos</div><div class="admin-kpi-value">{{ number_format($stats['students']) }}</div></div>
      </div>
    </article>
    <article class="admin-kpi-card">
      <div class="kpi-row">
        <span class="icon-badge"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
        <div><div class="admin-kpi-label">Matrículas</div><div class="admin-kpi-value">{{ number_format($stats['enrollments']) }}</div></div>
      </div>
    </article>
    <article class="admin-kpi-card">
      <div class="kpi-row">
        <span class="icon-badge"><i class="fas fa-book" aria-hidden="true"></i></span>
        <div><div class="admin-kpi-label">Cursos</div><div class="admin-kpi-value">{{ number_format($stats['courses']) }}</div></div>
      </div>
    </article>
    <article class="admin-kpi-card">
      <div class="kpi-row">
        <span class="icon-badge"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i></span>
        <div><div class="admin-kpi-label">Asignaciones docentes</div><div class="admin-kpi-value">{{ number_format($stats['assignments']) }}</div></div>
      </div>
    </article>
  </section>

  <section class="admin-card" aria-labelledby="directorActionsTitle">
    <h3 id="directorActionsTitle">Accesos de gestión académica</h3>
    <p class="muted-note">Administra alumnos, matrículas, cursos, asignaciones docentes y recursos pedagógicos.</p>
    <div class="admin-actions-row">
      <a class="btn btn-primary" href="{{ route('admin.students.index') }}"><i class="fas fa-user-graduate" aria-hidden="true"></i> Alumnos</a>
      <a class="btn btn-primary" href="{{ route('admin.enrollments.index') }}"><i class="fas fa-clipboard-check" aria-hidden="true"></i> Matrículas</a>
      <a class="btn btn-primary" href="{{ route('admin.courses.index') }}"><i class="fas fa-book" aria-hidden="true"></i> Cursos</a>
      <a class="btn btn-primary" href="{{ route('admin.assignments.create') }}"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i> Asignaciones</a>
      <a class="btn btn-primary" href="{{ route('director.resources.index') }}"><i class="fas fa-folder-open" aria-hidden="true"></i> Recursos pedagógicos ({{ number_format($stats['resources']) }})</a>
    </div>
  </section>
@endsection
