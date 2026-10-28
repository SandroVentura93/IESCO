@extends('layouts.admin')

@php
  $adminSection = 'students';
@endphp

@push('styles')
  <style>
    .students-directory {
      --students-ink: #183b29;
      --students-green: #17683e;
      --students-muted: #68776c;
      --students-border: #e4ebe5;
      padding: clamp(1rem, 2vw, 1.6rem) !important;
      border: 1px solid var(--students-border) !important;
      border-radius: 20px !important;
      background: linear-gradient(150deg, #fff, #fbfdfb) !important;
      box-shadow: 0 16px 40px rgba(20, 52, 34, .065) !important;
    }

    .students-directory__header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.15rem;
    }

    .students-directory__eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .42rem;
      color: #62816c;
      font-size: .68rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    .students-directory__header h1 {
      margin: .35rem 0 .3rem;
      color: var(--students-ink);
      font-size: clamp(1.55rem, 3vw, 2rem);
      letter-spacing: -.035em;
    }

    .students-directory__header p {
      margin: 0;
      color: var(--students-muted);
      font-size: .83rem;
      line-height: 1.55;
    }

    .students-directory__create {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      min-height: 42px !important;
      padding: .65rem .9rem !important;
      border-radius: 10px !important;
      font-size: .77rem !important;
      font-weight: 750 !important;
    }

    .students-directory__header-actions {
      display: flex;
      flex: 0 0 auto;
      flex-wrap: wrap;
      align-items: center;
      justify-content: flex-end;
      gap: .45rem;
    }

    .students-directory__enrollments-link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      min-height: 42px;
      padding: .6rem .8rem;
      border: 1px solid #dfe7df;
      border-radius: 10px;
      background: #fff;
      color: #42614a;
      font-size: .74rem;
      font-weight: 750;
      text-decoration: none;
    }

    .students-directory__enrollments-link:hover {
      border-color: #b6cfb9;
      background: #eff7f0;
      color: #17683e;
    }

    .students-directory__alerts {
      display: grid;
      gap: .55rem;
      margin-bottom: 1rem;
    }

    .students-directory__alert {
      padding: .72rem .9rem;
      border: 1px solid #cce6d3;
      border-radius: 10px;
      background: #f1faf3;
      color: #245a37;
      font-size: .78rem;
    }

    .students-directory__alert--error {
      border-color: #f0d0cc;
      background: #fff5f3;
      color: #923d32;
    }

    .students-directory__stats {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: .7rem;
      margin-bottom: 1rem;
    }

    .students-directory__stat {
      display: flex;
      min-width: 0;
      align-items: center;
      gap: .7rem;
      padding: .78rem .9rem;
      border: 1px solid var(--students-border);
      border-radius: 12px;
      background: #fff;
    }

    .students-directory__stat-icon {
      display: grid;
      width: 38px;
      height: 38px;
      flex: 0 0 38px;
      place-items: center;
      border-radius: 11px;
      background: #eff6f0;
      color: var(--students-green);
    }

    .students-directory__stat:nth-child(2) .students-directory__stat-icon {
      background: #edf3fa;
      color: #486b91;
    }

    .students-directory__stat:nth-child(3) .students-directory__stat-icon {
      background: #fbf5e8;
      color: #927434;
    }

    .students-directory__stat:nth-child(4) .students-directory__stat-icon {
      background: #f5f0fa;
      color: #75538f;
    }

    .students-directory__stat-copy span,
    .students-directory__stat-copy strong {
      display: block;
    }

    .students-directory__stat-copy span {
      color: var(--students-muted);
      font-size: .68rem;
      font-weight: 650;
    }

    .students-directory__stat-copy strong {
      margin-top: .04rem;
      color: var(--students-ink);
      font-family: Georgia, 'Times New Roman', serif;
      font-size: 1.25rem;
      line-height: 1.2;
    }

    .students-directory__toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: .8rem;
      padding: .75rem;
      border: 1px solid var(--students-border);
      border-radius: 12px;
      background: #f8faf8;
    }

    .students-directory__toolbar-copy strong,
    .students-directory__toolbar-copy span {
      display: block;
    }

    .students-directory__toolbar-copy strong {
      color: #304736;
      font-size: .76rem;
    }

    .students-directory__toolbar-copy span {
      margin-top: .1rem;
      color: var(--students-muted);
      font-size: .68rem;
    }

    .students-directory__filters {
      display: flex;
      flex: 0 1 600px;
      align-items: center;
      gap: .45rem;
      min-width: 250px;
    }

    .students-directory__filters input,
    .students-directory__filters select {
      min-width: 0;
      min-height: 38px;
      padding: .5rem .65rem;
      border: 1px solid #dfe7df;
      border-radius: 9px;
      background: #fff;
      color: #294333;
      font: inherit;
      font-size: .74rem;
    }

    .students-directory__filters input {
      flex: 1 1 250px;
    }

    .students-directory__filters select {
      flex: 0 0 150px;
    }

    .students-directory__filters input:focus,
    .students-directory__filters select:focus {
      outline: 3px solid rgba(23, 104, 62, .13);
      border-color: #8daf93;
    }

    .students-directory__filters button,
    .students-directory__clear {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .35rem;
      min-height: 38px;
      padding: .5rem .7rem;
      border: 0;
      border-radius: 9px;
      background: var(--students-green);
      color: #fff;
      font-size: .72rem;
      font-weight: 750;
      text-decoration: none;
      white-space: nowrap;
    }

    .students-directory__clear {
      border: 1px solid #dfe7df;
      background: #fff;
      color: #5c6d60;
    }

    .students-directory__table-wrap {
      overflow: auto;
      border: 1px solid var(--students-border);
      border-radius: 13px;
      background: #fff;
      scrollbar-width: thin;
    }

    .students-directory__table {
      width: 100%;
      min-width: 650px;
      border-collapse: separate;
      border-spacing: 0;
      color: #4d5b50;
      font-size: .76rem;
    }

    .students-directory__table thead th {
      padding: .75rem .85rem;
      border-bottom: 1px solid var(--students-border);
      background: #f6f9f6;
      color: #68776c;
      font-size: .65rem;
      font-weight: 800;
      letter-spacing: .06em;
      text-align: left;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .students-directory__table tbody td {
      padding: .72rem .85rem;
      border-bottom: 1px solid #edf1ed;
      vertical-align: middle;
    }

    .students-directory__table tbody tr:last-child td {
      border-bottom: 0;
    }

    .students-directory__table tbody tr:hover {
      background: #fafcfb;
    }

    .students-directory__account {
      color: #758176;
      font-size: .66rem;
    }

    .students-directory__pending {
      margin-bottom: .9rem;
      padding: .85rem;
      border: 1px solid #eee4c9;
      border-radius: 12px;
      background: #fffcf4;
    }

    .students-directory__pending-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: .75rem;
      margin-bottom: .65rem;
    }

    .students-directory__pending-header strong {
      display: block;
      color: #594b2a;
      font-size: .76rem;
    }

    .students-directory__pending-header p {
      margin: .15rem 0 0;
      color: #7a7058;
      font-size: .68rem;
    }

    .students-directory__pending-count {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 26px;
      height: 26px;
      padding: 0 .4rem;
      border: 1px solid #eee0b8;
      border-radius: 999px;
      background: #fff;
      color: #826d36;
      font-size: .7rem;
      font-weight: 800;
    }

    .students-directory__pending-list {
      display: grid;
      gap: .4rem;
    }

    .students-directory__pending-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      padding: .55rem .65rem;
      border: 1px solid #f0e9d7;
      border-radius: 9px;
      background: rgba(255, 255, 255, .8);
    }

    .students-directory__pending-user strong,
    .students-directory__pending-user span {
      display: block;
    }

    .students-directory__pending-user strong {
      color: #3e4939;
      font-size: .72rem;
    }

    .students-directory__pending-user span {
      margin-top: .1rem;
      color: #7a786e;
      font-size: .66rem;
      overflow-wrap: anywhere;
    }

    .students-directory__complete {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .32rem;
      min-height: 32px;
      padding: .38rem .58rem;
      border: 1px solid #e4d7b2;
      border-radius: 8px;
      background: #fff;
      color: #67572d;
      font-size: .67rem;
      font-weight: 750;
      text-decoration: none;
    }

    .students-directory__complete:hover {
      border-color: #d1bd82;
      background: #fff9e9;
      color: #50421f;
    }

    .students-directory__name {
      display: flex;
      align-items: center;
      gap: .6rem;
      min-width: 170px;
    }

    .students-directory__avatar {
      display: grid;
      width: 34px;
      height: 34px;
      flex: 0 0 34px;
      place-items: center;
      border: 1px solid #deebdf;
      border-radius: 10px;
      background: linear-gradient(145deg, #edf6ee, #f7f3e9);
      color: #37704a;
      font-size: .72rem;
      font-weight: 800;
    }

    .students-directory__name strong,
    .students-directory__name small {
      display: block;
    }

    .students-directory__name strong {
      color: #263c2d;
      font-size: .76rem;
      font-weight: 750;
    }

    .students-directory__name small {
      margin-top: .08rem;
      color: #78857a;
      font-size: .66rem;
    }

    .students-directory__grade {
      display: inline-flex;
      padding: .27rem .52rem;
      border: 1px solid #dfe9df;
      border-radius: 999px;
      background: #f5f9f5;
      color: #48634e;
      font-size: .68rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .students-directory__year-status {
      display: inline-flex;
      padding: .27rem .5rem;
      border: 1px solid #dfe9df;
      border-radius: 999px;
      background: #f5f9f5;
      color: #48634e;
      font-size: .65rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .students-directory__actions {
      display: flex;
      justify-content: flex-end;
      gap: .35rem;
    }

    .students-directory__action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .32rem;
      min-height: 32px !important;
      padding: .38rem .55rem !important;
      border: 1px solid #dde7de !important;
      border-radius: 8px !important;
      background: #fff !important;
      color: #42614a !important;
      font-size: .68rem !important;
      font-weight: 750 !important;
      text-decoration: none;
      box-shadow: none !important;
    }

    .students-directory__action:hover {
      border-color: #b6cfb9 !important;
      background: #eff7f0 !important;
      color: #17683e !important;
    }

    .students-directory__action--delete {
      border-color: #eedbda !important;
      color: #a24a42 !important;
    }

    .students-directory__action--delete:hover {
      border-color: #ecc3bf !important;
      background: #fff4f2 !important;
      color: #922e27 !important;
    }

    .students-directory__empty {
      padding: 2.2rem 1rem;
      text-align: center;
    }

    .students-directory__empty-icon {
      display: grid;
      width: 44px;
      height: 44px;
      margin: 0 auto .7rem;
      place-items: center;
      border-radius: 13px;
      background: #eff6f0;
      color: #37704a;
      font-size: 1rem;
    }

    .students-directory__empty strong {
      display: block;
      color: var(--students-ink);
      font-size: .86rem;
    }

    .students-directory__empty p {
      margin: .25rem 0 .7rem;
      color: var(--students-muted);
      font-size: .75rem;
    }

    .students-directory__empty a {
      color: var(--students-green);
      font-size: .74rem;
      font-weight: 750;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .students-directory__results {
      margin-top: .65rem;
      color: var(--students-muted);
      font-size: .7rem;
    }

    .students-directory .admin-pagination {
      margin-top: .75rem;
    }

    @media (max-width: 900px) {
      .students-directory__stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .students-directory__toolbar {
        align-items: flex-start;
        flex-direction: column;
      }

      .students-directory__filters {
        flex-basis: auto;
        width: 100%;
      }
    }

    @media (max-width: 600px) {
      .students-directory__header {
        align-items: stretch;
        flex-direction: column;
      }

      .students-directory__header-actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
      }

      .students-directory__create {
        width: 100%;
      }

      .students-directory__enrollments-link {
        width: 100%;
      }

      .students-directory__stats {
        gap: .45rem;
      }

      .students-directory__stat {
        align-items: flex-start;
        flex-direction: column;
        gap: .4rem;
        padding: .6rem;
      }

      .students-directory__stat-icon {
        width: 32px;
        height: 32px;
      }

      .students-directory__stat-copy span {
        min-height: 2em;
      }

      .students-directory__filters {
        flex-wrap: wrap;
      }

      .students-directory__filters input {
        flex-basis: 100%;
      }

      .students-directory__filters select {
        flex: 1 1 120px;
      }

      .students-directory__filters button,
      .students-directory__clear {
        flex: 1 0 auto;
      }

      .students-directory__pending-row {
        align-items: flex-start;
        flex-direction: column;
      }

      .students-directory__complete {
        width: 100%;
      }
    }
  </style>
@endpush

@section('admin_content')
  <section class="admin-card students-directory" aria-labelledby="studentsTitle">
    <header class="students-directory__header">
      <div>
        <span class="students-directory__eyebrow"><i class="fas fa-user-graduate" aria-hidden="true"></i> Gestión académica</span>
        <h1 id="studentsTitle">Directorio de alumnos</h1>
        <p>Consulta y administra los datos de matrícula de los estudiantes.</p>
      </div>
      <div class="students-directory__header-actions">
        <a href="{{ route('admin.enrollments.index') }}" class="students-directory__enrollments-link">
          <i class="fas fa-clipboard-check" aria-hidden="true"></i> Ver matrículas
        </a>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary students-directory__create">
          <i class="fas fa-plus" aria-hidden="true"></i> Registrar alumno
        </a>
      </div>
    </header>

    @if(session('success'))
      <div class="students-directory__alerts" role="status">
        <div class="students-directory__alert">{{ session('success') }}</div>
      </div>
    @endif

    @if($errors->any())
      <div class="students-directory__alerts" role="alert">
        @foreach($errors->all() as $error)
          <div class="students-directory__alert students-directory__alert--error">{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <div class="students-directory__stats" aria-label="Resumen del directorio">
      <div class="students-directory__stat">
        <span class="students-directory__stat-icon"><i class="fas fa-users" aria-hidden="true"></i></span>
        <span class="students-directory__stat-copy"><span>Fichas de matrícula</span><strong>{{ number_format($stats['total']) }}</strong></span>
      </div>
      <div class="students-directory__stat">
        <span class="students-directory__stat-icon"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
        <span class="students-directory__stat-copy"><span>Grados registrados</span><strong>{{ number_format($stats['grades']) }}</strong></span>
      </div>
      <div class="students-directory__stat">
        <span class="students-directory__stat-icon"><i class="fas fa-circle-exclamation" aria-hidden="true"></i></span>
        <span class="students-directory__stat-copy"><span>Sin grado asignado</span><strong>{{ number_format($stats['without_grade']) }}</strong></span>
      </div>
      <div class="students-directory__stat">
        <span class="students-directory__stat-icon"><i class="fas fa-link" aria-hidden="true"></i></span>
        <span class="students-directory__stat-copy"><span>Cuentas sin ficha</span><strong>{{ number_format($stats['pending_accounts']) }}</strong></span>
      </div>
    </div>

    @if($pendingStudentUsers->isNotEmpty())
      <section class="students-directory__pending" aria-labelledby="pendingStudentsTitle">
        <header class="students-directory__pending-header">
          <div>
            <strong id="pendingStudentsTitle">Cuentas de alumno pendientes de ficha</strong>
            <p>Completa los datos de matrícula para que aparezcan en el directorio.</p>
          </div>
          <span class="students-directory__pending-count" aria-label="{{ $pendingStudentUsers->count() }} cuentas pendientes">{{ $pendingStudentUsers->count() }}</span>
        </header>
        <div class="students-directory__pending-list">
          @foreach($pendingStudentUsers as $pendingUser)
            <div class="students-directory__pending-row">
              <span class="students-directory__pending-user">
                <strong>{{ $pendingUser->name }}</strong>
                <span>{{ $pendingUser->email }}</span>
              </span>
              <a href="{{ route('admin.students.create', ['user_id' => $pendingUser->id]) }}" class="students-directory__complete">
                <i class="fas fa-link" aria-hidden="true"></i> Completar ficha
              </a>
            </div>
          @endforeach
        </div>
      </section>
    @endif

    <div class="students-directory__toolbar">
      <div class="students-directory__toolbar-copy">
        <strong>Buscar en el directorio</strong>
        <span>Filtra por DNI, nombres, apellidos o grado.</span>
      </div>
      <form method="GET" action="{{ route('admin.students.index') }}" class="students-directory__filters" role="search">
        <label class="sr-only" for="studentSearch">Buscar alumnos</label>
        <input id="studentSearch" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="DNI, nombre o apellido" />
        <label class="sr-only" for="studentGrade">Filtrar por grado</label>
        <select id="studentGrade" name="grade">
          <option value="">Todos los grados</option>
          @foreach($grades as $availableGrade)
            <option value="{{ $availableGrade }}" @selected($grade === $availableGrade)>{{ $availableGrade }}</option>
          @endforeach
        </select>
        <button type="submit"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
        @if($search !== '' || $grade !== '')
          <a href="{{ route('admin.students.index') }}" class="students-directory__clear">Limpiar</a>
        @endif
      </form>
    </div>

    <div class="students-directory__table-wrap">
      @if($students->isEmpty())
        <div class="students-directory__empty">
          <span class="students-directory__empty-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
          <strong>{{ $search !== '' || $grade !== '' ? 'No encontramos alumnos' : ($pendingStudentUsers->isNotEmpty() ? 'Hay cuentas pendientes de ficha' : 'Aún no hay alumnos registrados') }}</strong>
          <p>{{ $search !== '' || $grade !== '' ? 'Prueba con otros datos o limpia los filtros.' : ($pendingStudentUsers->isNotEmpty() ? 'Completa la ficha de matrícula pendiente para que el alumno aparezca aquí.' : 'Registra el primer alumno para comenzar el directorio.') }}</p>
          @if($search !== '' || $grade !== '')
            <a href="{{ route('admin.students.index') }}">Limpiar búsqueda</a>
          @else
            <a href="{{ route('admin.students.create') }}">Registrar alumno</a>
          @endif
        </div>
      @else
        <table class="students-directory__table">
          <thead>
            <tr>
              <th>Alumno</th>
              <th>DNI</th>
              <th>Grado registrado</th>
              <th>Matrícula {{ now()->year }}</th>
              <th><span class="sr-only">Acciones</span></th>
            </tr>
          </thead>
          <tbody>
            @foreach($students as $student)
              <tr>
                <td>
                  <div class="students-directory__name">
                    <span class="students-directory__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student->first_name, 0, 1)) }}{{ mb_strtoupper(mb_substr($student->last_name, 0, 1)) }}</span>
                    <span><strong>{{ $student->last_name }}, {{ $student->first_name }}</strong><small>{{ $student->user?->email ?? 'Sin cuenta de acceso vinculada' }}</small></span>
                  </div>
                </td>
                <td>{{ $student->dni }}</td>
                <td><span class="students-directory__grade">{{ $student->grade ?: 'Sin grado' }}</span></td>
                <td>
                  @if($student->current_year_enrollments_count > 0)
                    <a href="{{ route('admin.enrollments.index', ['q' => $student->dni]) }}" class="students-directory__year-status">Ver historial</a>
                  @else
                    <span class="students-directory__year-status">Pendiente</span>
                  @endif
                </td>
                <td>
                  <div class="students-directory__actions">
                    @if($student->current_year_enrollments_count === 0)
                      <a href="{{ route('admin.enrollments.create', ['student_id' => $student->id]) }}" class="students-directory__action" aria-label="Matricular a {{ $student->first_name }} {{ $student->last_name }}">
                        <i class="fas fa-clipboard-check" aria-hidden="true"></i><span>Matricular</span>
                      </a>
                    @endif
                    <a href="{{ route('admin.students.edit', $student->id) }}" class="students-directory__action" aria-label="Editar a {{ $student->first_name }} {{ $student->last_name }}">
                      <i class="fas fa-pen" aria-hidden="true"></i><span>Editar</span>
                    </a>
                    @if($student->current_year_enrollments_count === 0)
                      <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" onsubmit="return confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button class="students-directory__action students-directory__action--delete" type="submit" aria-label="Eliminar a {{ $student->first_name }} {{ $student->last_name }}">
                          <i class="fas fa-trash" aria-hidden="true"></i><span>Eliminar</span>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <p class="students-directory__results" role="status">
      Mostrando {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} de {{ $students->total() }} fichas de matrícula.
    </p>
    @if($students->hasPages())
      <div class="admin-pagination">{{ $students->links() }}</div>
    @endif
  </section>
@endsection
