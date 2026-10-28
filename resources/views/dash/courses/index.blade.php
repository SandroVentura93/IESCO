@extends('layouts.admin')

@php
  $adminSection = 'courses';
@endphp

@push('styles')
  <style>
    .courses-directory {
      container: courses-directory / inline-size;
      --courses-green: #17683e;
      --courses-ink: #183b29;
      --courses-muted: #68776c;
      --courses-border: #e6ece6;
      padding: clamp(1rem, 2vw, 1.6rem) !important;
      border: 1px solid var(--courses-border) !important;
      border-radius: 20px !important;
      background: linear-gradient(150deg, #fff, #fbfdfb) !important;
      box-shadow: 0 16px 40px rgba(20, 52, 34, .07) !important;
    }

    .courses-directory__header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1.5rem;
      margin-bottom: 1.35rem;
    }

    .courses-directory__eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      color: #62816c;
      font-size: .7rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    .courses-directory__header h1 {
      margin: .35rem 0 .4rem;
      color: var(--courses-ink);
      font-size: clamp(1.6rem, 3vw, 2.05rem);
      letter-spacing: -.035em;
    }

    .courses-directory__header p {
      color: var(--courses-muted);
      font-size: .88rem;
    }

    .courses-directory__create {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .55rem;
      min-height: 43px !important;
      padding: .7rem .95rem !important;
      border-radius: 11px !important;
      font-size: .79rem !important;
      font-weight: 750 !important;
    }

    .courses-directory__stats {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: .75rem;
      margin-bottom: 1.15rem;
    }

    .courses-directory__stat {
      display: flex;
      align-items: center;
      gap: .8rem;
      min-width: 0;
      padding: .9rem 1rem;
      border: 1px solid var(--courses-border);
      border-radius: 13px;
      background: #fff;
    }

    .courses-directory__stat-icon,
    .courses-directory__course-icon {
      display: grid;
      flex: 0 0 auto;
      place-items: center;
      width: 39px;
      height: 39px;
      border-radius: 11px;
      background: #eff6f0;
      color: var(--courses-green);
    }

    .courses-directory__stat:nth-child(2) .courses-directory__stat-icon {
      background: #edf3fa;
      color: #486b91;
    }

    .courses-directory__stat:nth-child(3) .courses-directory__stat-icon {
      background: #f2eef8;
      color: #766095;
    }

    .courses-directory__stat:nth-child(4) .courses-directory__stat-icon {
      background: #fbf5e8;
      color: #927434;
    }

    .courses-directory__stat-copy span,
    .courses-directory__stat-copy strong {
      display: block;
    }

    .courses-directory__stat-copy span {
      color: var(--courses-muted);
      font-size: .7rem;
      font-weight: 650;
    }

    .courses-directory__stat-copy strong {
      margin-top: .05rem;
      color: var(--courses-ink);
      font-family: var(--font-serif);
      font-size: 1.3rem;
      line-height: 1.2;
    }

    .courses-directory__toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: .9rem;
      padding: .8rem;
      border: 1px solid var(--courses-border);
      border-radius: 13px;
      background: #f8faf8;
    }

    .courses-directory__toolbar-copy strong,
    .courses-directory__toolbar-copy span {
      display: block;
    }

    .courses-directory__toolbar-copy strong {
      color: #304736;
      font-size: .78rem;
    }

    .courses-directory__toolbar-copy span {
      margin-top: .12rem;
      color: var(--courses-muted);
      font-size: .69rem;
    }

    .courses-directory__search {
      display: flex;
      flex: 0 1 355px;
      align-items: center;
      gap: .45rem;
      min-width: 230px;
    }

    .courses-directory__search label {
      position: absolute;
      width: 1px;
      height: 1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
    }

    .courses-directory__search input {
      width: 100%;
      min-width: 0;
      height: 38px;
      padding: .55rem .7rem;
      border: 1px solid #dfe7df;
      border-radius: 9px;
      background: #fff;
      color: #294333;
      font: inherit;
      font-size: .76rem;
    }

    .courses-directory__search input:focus {
      outline: 3px solid rgba(23, 104, 62, .14);
      border-color: #8daf93;
    }

    .courses-directory__search button,
    .courses-directory__clear {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      min-height: 38px;
      padding: .55rem .75rem;
      border-radius: 9px;
      background: #17683e;
      color: #fff;
      font-size: .74rem;
      font-weight: 750;
    }

    .courses-directory__clear {
      border: 1px solid #dfe7df;
      background: #fff;
      color: #5c6d60;
    }

    .courses-directory__table-wrap {
      padding: 0 !important;
      overflow: auto;
      border: 1px solid var(--courses-border) !important;
      border-radius: 14px !important;
      background: #fff !important;
      box-shadow: none !important;
      scrollbar-width: thin;
    }

    .courses-directory__table {
      width: 100% !important;
      min-width: 750px;
      border-collapse: separate !important;
      border-spacing: 0 !important;
      table-layout: auto;
    }

    .admin-courses .courses-directory__table thead {
      display: table-header-group !important;
      padding: 0 !important;
      background: #f7faf7;
      color: #68776c;
      font-size: .67rem;
      letter-spacing: .07em;
      text-transform: uppercase;
    }

    .courses-directory__table thead th {
      padding: .8rem .9rem !important;
      border-bottom: 1px solid var(--courses-border) !important;
      text-align: left;
      white-space: nowrap;
    }

    .admin-courses .courses-directory__table tbody {
      display: table-row-group !important;
    }

    .admin-courses .courses-directory__table tbody tr {
      display: table-row !important;
      margin: 0 !important;
      border-radius: 0 !important;
      background: #fff;
      box-shadow: none !important;
      transition: background .15s ease;
    }

    .courses-directory__table tbody tr:hover {
      background: #f9fbf9 !important;
      transform: none !important;
    }

    .admin-courses .courses-directory__table tbody td {
      padding: .78rem .9rem !important;
      border-bottom: 1px solid #edf1ed !important;
      color: #4d5b50;
      vertical-align: middle !important;
    }

    .courses-directory__table tbody tr:last-child td {
      border-bottom: 0 !important;
    }

    .courses-directory__course {
      display: flex;
      align-items: center;
      gap: .7rem;
      min-width: 170px;
    }

    .courses-directory__course-icon {
      width: 36px;
      height: 36px;
      border: 1px solid #deebdf;
      background: linear-gradient(145deg, #edf6ee, #f7f3e9);
    }

    .courses-directory__course-copy strong,
    .courses-directory__course-copy span {
      display: block;
    }

    .courses-directory__course-copy strong {
      color: #263c2d;
      font-size: .78rem;
      font-weight: 750;
    }

    .courses-directory__course-copy span {
      margin-top: .13rem;
      color: #78857a;
      font-size: .68rem;
    }

    .courses-directory__teachers {
      display: flex;
      flex-wrap: wrap;
      gap: .3rem;
      min-width: 145px;
    }

    .courses-directory__teacher-chip,
    .courses-directory__count,
    .courses-directory__unused {
      display: inline-flex;
      align-items: center;
      gap: .32rem;
      padding: .3rem .5rem;
      border: 1px solid #dfe9df;
      border-radius: 999px;
      background: #f5f9f5;
      color: #48634e;
      font-size: .66rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .courses-directory__teachers-empty,
    .courses-directory__muted {
      color: #758176;
      font-size: .72rem;
    }

    .courses-directory__count--resources {
      border-color: #e6def0;
      background: #f6f2fa;
      color: #6c5689;
    }

    .courses-directory__count--files {
      border-color: #e7e1d1;
      background: #fbf7eb;
      color: #826d36;
    }

    .courses-directory__actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: .4rem;
      min-width: 145px;
    }

    .courses-directory .courses-directory__actions .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .35rem;
      min-height: 32px !important;
      padding: .4rem .58rem !important;
      border: 1px solid #dde7de;
      border-radius: 8px !important;
      background: #fff;
      color: #42614a;
      font-size: .69rem !important;
      font-weight: 750;
      box-shadow: none;
      transition: background .18s ease, border-color .18s ease, color .18s ease;
    }

    .courses-directory .courses-directory__actions .btn:hover {
      transform: none;
      border-color: #b6cfb9;
      background: #eff7f0;
      color: #17683e;
      box-shadow: none;
    }

    .courses-directory .courses-directory__actions .btn--danger {
      border-color: #eedbda;
      color: #a24a42;
    }

    .courses-directory .courses-directory__actions .btn--danger:hover {
      border-color: #ecc3bf;
      background: #fff4f2;
      color: #922e27;
    }

    .courses-directory__unused {
      border-color: #eee4c9;
      background: #fcf8ed;
      color: #826d36;
      cursor: help;
    }

    .courses-directory__empty {
      padding: 2.5rem 1rem;
      text-align: center;
    }

    .courses-directory__empty-icon {
      display: grid;
      place-items: center;
      width: 46px;
      height: 46px;
      margin: 0 auto .75rem;
      border-radius: 14px;
      background: #eff6f0;
      color: #37704a;
      font-size: 1.1rem;
    }

    .courses-directory__empty strong {
      display: block;
      color: var(--courses-ink);
      font-size: .9rem;
    }

    .courses-directory__empty p {
      margin: .25rem 0 .75rem;
      color: var(--courses-muted);
      font-size: .78rem;
    }

    .courses-directory__empty a {
      color: var(--courses-green);
      font-size: .76rem;
      font-weight: 750;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .courses-directory__results,
    .courses-directory__hint {
      color: var(--courses-muted);
      font-size: .72rem;
    }

    .courses-directory__results {
      margin-top: .7rem;
    }

    .courses-directory__hint {
      display: flex;
      align-items: flex-start;
      gap: .45rem;
      margin-top: .7rem;
    }

    .courses-directory__hint i {
      margin-top: .15rem;
      color: #8a7744;
    }

    .courses-directory__alert {
      margin-bottom: .8rem;
      padding: .75rem .9rem;
      border: 1px solid #cce6d1;
      border-radius: 10px;
      background: #f0f8f1;
      color: #28633a;
      font-size: .8rem;
    }

    .courses-directory__alert--error {
      border-color: #f0d0cc;
      background: #fff5f3;
      color: #9c3d32;
    }

    .courses-directory .admin-pagination {
      margin-top: .75rem;
    }

    @media (max-width: 1000px) {
      .courses-directory__stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .courses-directory__toolbar {
        align-items: flex-start;
        flex-direction: column;
      }

      .courses-directory__search {
        flex-basis: auto;
        width: 100%;
      }
    }

    @media (max-width: 620px) {
      .courses-directory__header {
        align-items: stretch;
        flex-direction: column;
        gap: .8rem;
      }

      .courses-directory__create {
        width: 100%;
      }

      .courses-directory__stats {
        gap: .55rem;
      }

      .courses-directory__stat {
        gap: .55rem;
        padding: .7rem .65rem;
      }

      .courses-directory__stat-icon {
        width: 33px;
        height: 33px;
      }

      .courses-directory__stat-copy span {
        font-size: .65rem;
      }

      .courses-directory__search {
        min-width: 0;
      }
    }

    @container courses-directory (max-width: 760px) {
      .admin-courses .courses-directory__table {
        display: block;
        min-width: 0 !important;
      }

      .admin-courses .courses-directory__table thead {
        display: none !important;
      }

      .admin-courses .courses-directory__table tbody {
        display: grid !important;
        gap: .65rem;
        padding: .65rem;
      }

      .admin-courses .courses-directory__table tbody tr {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: .5rem .75rem;
        padding: .8rem;
        border: 1px solid var(--courses-border);
        border-radius: 12px !important;
        background: #fff;
      }

      .admin-courses .courses-directory__table tbody td {
        display: grid !important;
        gap: .2rem;
        min-width: 0;
        padding: 0 !important;
        border: 0 !important;
      }

      .admin-courses .courses-directory__table tbody td::before {
        content: attr(data-label);
        color: #7a887d;
        font-size: .61rem;
        font-weight: 750;
        letter-spacing: .06em;
        text-transform: uppercase;
      }

      .admin-courses .courses-directory__table tbody td:first-child,
      .admin-courses .courses-directory__table tbody td.courses-directory__actions {
        grid-column: 1 / -1;
      }

      .admin-courses .courses-directory__table tbody td.courses-directory__actions {
        justify-content: flex-start;
        min-width: 0;
        padding-top: .35rem !important;
        border-top: 1px solid #edf1ed !important;
      }

      .courses-directory__empty-cell::before {
        display: none;
      }
    }
  </style>
@endpush

@section('admin_content')
  <section class="admin-card admin-courses courses-directory" aria-labelledby="coursesDirectoryTitle">
    <header class="courses-directory__header">
      <div>
        <span class="courses-directory__eyebrow"><i class="fas fa-graduation-cap" aria-hidden="true"></i> Catálogo académico</span>
        <h1 id="coursesDirectoryTitle">Gestión de cursos</h1>
        <p>Organiza las asignaturas, consulta sus docentes y administra sus recursos.</p>
      </div>
      <a href="{{ route('admin.courses.create') }}" class="btn btn-primary courses-directory__create">
        <i class="fas fa-plus" aria-hidden="true"></i> Crear curso
      </a>
    </header>

    @if(session('success'))
      <div class="courses-directory__alert" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="courses-directory__alert courses-directory__alert--error" role="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="courses-directory__alert courses-directory__alert--error" role="alert">
        <strong>No se pudo completar la acción.</strong>
        <ul>
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <section class="courses-directory__stats" aria-label="Resumen de cursos y recursos">
      <article class="courses-directory__stat">
        <span class="courses-directory__stat-icon"><i class="fas fa-book-open" aria-hidden="true"></i></span>
        <div class="courses-directory__stat-copy"><span>Total de cursos</span><strong>{{ number_format($stats['courses']) }}</strong></div>
      </article>
      <article class="courses-directory__stat">
        <span class="courses-directory__stat-icon"><i class="fas fa-chalkboard-user" aria-hidden="true"></i></span>
        <div class="courses-directory__stat-copy"><span>Asignaciones docentes</span><strong>{{ number_format($stats['assignments']) }}</strong></div>
      </article>
      <article class="courses-directory__stat">
        <span class="courses-directory__stat-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></span>
        <div class="courses-directory__stat-copy"><span>Archivos y recursos</span><strong>{{ number_format($stats['resources']) }}</strong></div>
      </article>
      <article class="courses-directory__stat">
        <span class="courses-directory__stat-icon"><i class="fas fa-circle-exclamation" aria-hidden="true"></i></span>
        <div class="courses-directory__stat-copy"><span>Sin docente</span><strong>{{ number_format($stats['unassigned']) }}</strong></div>
      </article>
    </section>

    <div class="courses-directory__toolbar">
      <div class="courses-directory__toolbar-copy">
        <strong>Catálogo de cursos</strong>
        <span>Busca asignaturas y revisa quiénes las imparten.</span>
      </div>
      <form class="courses-directory__search" method="GET" action="{{ route('admin.courses.index') }}" role="search">
        <label for="coursesSearch">Buscar curso</label>
        <input id="coursesSearch" type="search" name="q" value="{{ $search }}" placeholder="Buscar por nombre del curso" maxlength="100" />
        <button type="submit"><i class="fas fa-search" aria-hidden="true"></i><span>Buscar</span></button>
        @if($search !== '')
          <a class="courses-directory__clear" href="{{ route('admin.courses.index') }}">Limpiar</a>
        @endif
      </form>
    </div>

    <div class="admin-table-wrap courses-directory__table-wrap">
      <table class="table admin-table courses-directory__table">
        <thead>
          <tr>
            <th scope="col">Curso</th>
            <th scope="col">Docentes asignados</th>
            <th scope="col">Recursos</th>
            <th scope="col">Archivos</th>
            <th scope="col"><span class="sr-only">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($courses as $course)
            @php
              $hasDependencies = $course->teachers_count > 0
                || $course->pedagogical_resources_count > 0
                || $course->stored_files_count > 0;
            @endphp
            <tr>
              <td data-label="Curso">
                <div class="courses-directory__course">
                  <span class="courses-directory__course-icon"><i class="fas fa-book" aria-hidden="true"></i></span>
                  <span class="courses-directory__course-copy">
                    <strong>{{ $course->name }}</strong>
                    <span>Curso #{{ $course->id }}</span>
                  </span>
                </div>
              </td>
              <td data-label="Docentes asignados">
                @if($course->teachers->isNotEmpty())
                  <div class="courses-directory__teachers">
                    @foreach($course->teachers->take(2) as $teacher)
                      <span class="courses-directory__teacher-chip"><i class="fas fa-user-tie" aria-hidden="true"></i>{{ $teacher->name }}</span>
                    @endforeach
                    @if($course->teachers_count > 2)
                      <span class="courses-directory__teacher-chip">+{{ $course->teachers_count - 2 }}</span>
                    @endif
                  </div>
                @else
                  <span class="courses-directory__teachers-empty">Pendiente de asignación</span>
                @endif
              </td>
              <td data-label="Recursos">
                <span class="courses-directory__count courses-directory__count--resources">
                  <i class="fas fa-folder-open" aria-hidden="true"></i>{{ number_format($course->pedagogical_resources_count) }}
                </span>
              </td>
              <td data-label="Archivos">
                <span class="courses-directory__count courses-directory__count--files">
                  <i class="fas fa-file" aria-hidden="true"></i>{{ number_format($course->stored_files_count) }}
                </span>
              </td>
              <td class="admin-actions-cell courses-directory__actions" data-label="Acciones">
                <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm" aria-label="Editar curso {{ $course->name }}">
                  <i class="fas fa-pen" aria-hidden="true"></i><span>Editar</span>
                </a>
                @if(!$hasDependencies)
                  <form method="POST" action="{{ route('admin.courses.destroy', $course->id) }}" class="confirm-delete admin-inline-form">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn--danger" type="submit" aria-label="Eliminar curso {{ $course->name }}">
                      <i class="fas fa-trash-can" aria-hidden="true"></i><span>Eliminar</span>
                    </button>
                  </form>
                @else
                  <span
                    class="courses-directory__unused"
                    title="Para eliminarlo, quita primero los docentes, los recursos y los archivos vinculados."
                    aria-label="No se puede eliminar mientras tenga docentes o recursos asociados"
                  ><i class="fas fa-link" aria-hidden="true"></i> En uso</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td class="courses-directory__empty-cell" colspan="5">
                <div class="courses-directory__empty">
                  <span class="courses-directory__empty-icon"><i class="fas fa-book-open" aria-hidden="true"></i></span>
                  <strong>{{ $search !== '' ? 'No encontramos cursos' : 'Todavía no hay cursos' }}</strong>
                  <p>{{ $search !== '' ? 'Prueba con otro nombre o limpia la búsqueda.' : 'Crea tu primer curso para empezar a organizar las asignaciones docentes.' }}</p>
                  @if($search !== '')
                    <a href="{{ route('admin.courses.index') }}">Limpiar búsqueda</a>
                  @else
                    <a href="{{ route('admin.courses.create') }}">Crear el primer curso</a>
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <p class="courses-directory__hint">
      <i class="fas fa-circle-info" aria-hidden="true"></i>
      Los cursos con docentes, archivos o recursos asociados quedan protegidos contra eliminación accidental.
    </p>
    @if($courses->total() > 0)
      <p class="courses-directory__results" aria-live="polite">
        Mostrando {{ $courses->firstItem() }}–{{ $courses->lastItem() }} de {{ number_format($courses->total()) }} cursos
      </p>
    @endif
    <div class="admin-pagination">{{ $courses->links() }}</div>
  </section>
@endsection
