@extends('layouts.admin')

@php($adminSection = 'enrollments')

@push('styles')
  <style>
    .enrollments-directory {
      --enroll-ink: #183b29;
      --enroll-green: #17683e;
      --enroll-muted: #68776c;
      --enroll-border: #e4ebe5;
      padding: clamp(1rem, 2vw, 1.6rem) !important;
      border: 1px solid var(--enroll-border) !important;
      border-radius: 20px !important;
      background: linear-gradient(150deg, #fff, #fbfdfb) !important;
      box-shadow: 0 16px 40px rgba(20, 52, 34, .065) !important;
    }

    .enrollments-directory__header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .enrollments-directory__eyebrow {
      color: #62816c;
      font-size: .68rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    .enrollments-directory__header h1 {
      margin: .3rem 0 .25rem;
      color: var(--enroll-ink);
      font-size: clamp(1.5rem, 3vw, 1.95rem);
      letter-spacing: -.035em;
    }

    .enrollments-directory__header p {
      margin: 0;
      color: var(--enroll-muted);
      font-size: .8rem;
      line-height: 1.5;
    }

    .enrollments-directory__create {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .42rem;
      min-height: 41px !important;
      padding: .62rem .82rem !important;
      border-radius: 10px !important;
      font-size: .74rem !important;
      font-weight: 750 !important;
    }

    .enrollments-directory__alerts {
      display: grid;
      gap: .45rem;
      margin-bottom: .8rem;
    }

    .enrollments-directory__alert {
      padding: .7rem .85rem;
      border: 1px solid #cce6d3;
      border-radius: 10px;
      background: #f1faf3;
      color: #245a37;
      font-size: .75rem;
    }

    .enrollments-directory__alert--error {
      border-color: #f0d0cc;
      background: #fff5f3;
      color: #923d32;
    }

    .enrollments-directory__stats {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: .65rem;
      margin-bottom: .85rem;
    }

    .enrollments-directory__stat {
      display: flex;
      min-width: 0;
      align-items: center;
      gap: .65rem;
      padding: .72rem .8rem;
      border: 1px solid var(--enroll-border);
      border-radius: 11px;
      background: #fff;
    }

    .enrollments-directory__stat-icon {
      display: grid;
      width: 35px;
      height: 35px;
      flex: 0 0 35px;
      place-items: center;
      border-radius: 10px;
      background: #eff6f0;
      color: var(--enroll-green);
    }

    .enrollments-directory__stat:nth-child(2) .enrollments-directory__stat-icon {
      background: #edf3fa;
      color: #486b91;
    }

    .enrollments-directory__stat:nth-child(3) .enrollments-directory__stat-icon {
      background: #eff7f0;
      color: #37704a;
    }

    .enrollments-directory__stat:nth-child(4) .enrollments-directory__stat-icon {
      background: #fcf3f1;
      color: #a24a42;
    }

    .enrollments-directory__stat-copy span,
    .enrollments-directory__stat-copy strong {
      display: block;
    }

    .enrollments-directory__stat-copy span {
      color: var(--enroll-muted);
      font-size: .65rem;
      font-weight: 650;
    }

    .enrollments-directory__stat-copy strong {
      margin-top: .05rem;
      color: var(--enroll-ink);
      font-family: Georgia, 'Times New Roman', serif;
      font-size: 1.18rem;
      line-height: 1.2;
    }

    .enrollments-directory__filters {
      display: flex;
      align-items: center;
      gap: .42rem;
      margin-bottom: .75rem;
      padding: .7rem;
      border: 1px solid var(--enroll-border);
      border-radius: 11px;
      background: #f8faf8;
    }

    .enrollments-directory__filters input,
    .enrollments-directory__filters select {
      min-width: 0;
      min-height: 37px;
      padding: .48rem .62rem;
      border: 1px solid #dfe7df;
      border-radius: 8px;
      background: #fff;
      color: #294333;
      font: inherit;
      font-size: .72rem;
    }

    .enrollments-directory__filters input {
      flex: 1 1 220px;
    }

    .enrollments-directory__filters select {
      flex: 0 1 140px;
    }

    .enrollments-directory__filters button,
    .enrollments-directory__clear {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .3rem;
      min-height: 37px;
      padding: .48rem .65rem;
      border: 0;
      border-radius: 8px;
      background: var(--enroll-green);
      color: #fff;
      font-size: .7rem;
      font-weight: 750;
      text-decoration: none;
      white-space: nowrap;
    }

    .enrollments-directory__clear {
      border: 1px solid #dfe7df;
      background: #fff;
      color: #5c6d60;
    }

    .enrollments-directory__table-wrap {
      overflow: auto;
      border: 1px solid var(--enroll-border);
      border-radius: 12px;
      background: #fff;
      scrollbar-width: thin;
    }

    .enrollments-directory__table {
      width: 100%;
      min-width: 900px;
      border-collapse: separate;
      border-spacing: 0;
      color: #4d5b50;
      font-size: .72rem;
    }

    .enrollments-directory__table thead th {
      padding: .72rem .75rem;
      border-bottom: 1px solid var(--enroll-border);
      background: #f6f9f6;
      color: #68776c;
      font-size: .62rem;
      font-weight: 800;
      letter-spacing: .055em;
      text-align: left;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .enrollments-directory__table tbody td {
      padding: .68rem .75rem;
      border-bottom: 1px solid #edf1ed;
      vertical-align: middle;
    }

    .enrollments-directory__table tbody tr:last-child td {
      border-bottom: 0;
    }

    .enrollments-directory__table tbody tr:hover {
      background: #fafcfb;
    }

    .enrollments-directory__student strong,
    .enrollments-directory__student span,
    .enrollments-directory__code strong,
    .enrollments-directory__code span {
      display: block;
    }

    .enrollments-directory__student strong {
      color: #263c2d;
      font-size: .74rem;
    }

    .enrollments-directory__student span,
    .enrollments-directory__code span {
      margin-top: .1rem;
      color: #78857a;
      font-size: .64rem;
    }

    .enrollments-directory__code strong {
      color: #46634c;
      font-size: .68rem;
      white-space: nowrap;
    }

    .enrollments-directory__status {
      display: inline-flex;
      align-items: center;
      gap: .32rem;
      padding: .27rem .5rem;
      border: 1px solid #e4e9e4;
      border-radius: 999px;
      background: #f7f9f7;
      color: #5c6a5e;
      font-size: .65rem;
      font-weight: 750;
      white-space: nowrap;
    }

    .enrollments-directory__status--draft {
      border-color: #e7dfc6;
      background: #fcf8ed;
      color: #826d36;
    }

    .enrollments-directory__status--enrolled {
      border-color: #cfe4d2;
      background: #f0f8f1;
      color: #28633a;
    }

    .enrollments-directory__status--cancelled {
      border-color: #eed8d5;
      background: #fff4f2;
      color: #a24a42;
    }

    .enrollments-directory__actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: .3rem;
      min-width: 230px;
    }

    .enrollments-directory__action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .3rem;
      min-height: 30px;
      padding: .32rem .48rem;
      border: 1px solid #dde7de;
      border-radius: 7px;
      background: #fff;
      color: #42614a;
      font-size: .64rem;
      font-weight: 750;
      text-decoration: none;
      white-space: nowrap;
    }

    .enrollments-directory__action:hover {
      border-color: #b6cfb9;
      background: #eff7f0;
      color: #17683e;
    }

    .enrollments-directory__action--confirm {
      border-color: #cfe4d2;
      background: #f0f8f1;
      color: #28633a;
    }

    .enrollments-directory__action--cancel {
      border-color: #eed8d5;
      color: #a24a42;
    }

    .enrollments-directory__empty {
      padding: 2.2rem 1rem;
      text-align: center;
    }

    .enrollments-directory__empty-icon {
      display: grid;
      width: 44px;
      height: 44px;
      margin: 0 auto .65rem;
      place-items: center;
      border-radius: 13px;
      background: #eff6f0;
      color: #37704a;
    }

    .enrollments-directory__empty strong {
      display: block;
      color: var(--enroll-ink);
      font-size: .85rem;
    }

    .enrollments-directory__empty p {
      margin: .25rem 0 .65rem;
      color: var(--enroll-muted);
      font-size: .73rem;
    }

    .enrollments-directory__empty a {
      color: var(--enroll-green);
      font-size: .72rem;
      font-weight: 750;
    }

    .enrollments-directory__results {
      margin-top: .65rem;
      color: var(--enroll-muted);
      font-size: .68rem;
    }

    .enrollments-directory .admin-pagination {
      margin-top: .75rem;
    }

    @media (max-width: 950px) {
      .enrollments-directory__stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .enrollments-directory__filters {
        flex-wrap: wrap;
      }

      .enrollments-directory__filters input {
        flex-basis: 100%;
      }
    }

    @media (max-width: 600px) {
      .enrollments-directory__header {
        align-items: stretch;
        flex-direction: column;
      }

      .enrollments-directory__create {
        width: 100%;
      }

      .enrollments-directory__filters select {
        flex: 1 1 110px;
      }

      .enrollments-directory__filters button,
      .enrollments-directory__clear {
        flex: 1 0 auto;
      }
    }
  </style>
@endpush

@section('admin_content')
  <section class="admin-card enrollments-directory" aria-labelledby="enrollmentsTitle">
    <header class="enrollments-directory__header">
      <div>
        <span class="enrollments-directory__eyebrow"><i class="fas fa-clipboard-check" aria-hidden="true"></i> Gestión académica</span>
        <h1 id="enrollmentsTitle">Matrículas</h1>
        <p>Organiza las matrículas por año lectivo, revisa su estado y genera fichas imprimibles.</p>
      </div>
      <a href="{{ route('admin.enrollments.create', ['academic_year' => $academicYear]) }}" class="btn btn-primary enrollments-directory__create">
        <i class="fas fa-plus" aria-hidden="true"></i> Nueva matrícula
      </a>
    </header>

    @if(session('success'))
      <div class="enrollments-directory__alerts" role="status">
        <div class="enrollments-directory__alert">{{ session('success') }}</div>
      </div>
    @endif
    @if($errors->any())
      <div class="enrollments-directory__alerts" role="alert">
        @foreach($errors->all() as $error)
          <div class="enrollments-directory__alert enrollments-directory__alert--error">{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <div class="enrollments-directory__stats" aria-label="Resumen de matrícula para {{ $academicYear }}">
      <div class="enrollments-directory__stat"><span class="enrollments-directory__stat-icon"><i class="fas fa-list-check" aria-hidden="true"></i></span><span class="enrollments-directory__stat-copy"><span>Total {{ $academicYear }}</span><strong>{{ number_format($stats['total']) }}</strong></span></div>
      <div class="enrollments-directory__stat"><span class="enrollments-directory__stat-icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span><span class="enrollments-directory__stat-copy"><span>Borradores</span><strong>{{ number_format($stats['draft']) }}</strong></span></div>
      <div class="enrollments-directory__stat"><span class="enrollments-directory__stat-icon"><i class="fas fa-circle-check" aria-hidden="true"></i></span><span class="enrollments-directory__stat-copy"><span>Matriculados</span><strong>{{ number_format($stats['enrolled']) }}</strong></span></div>
      <div class="enrollments-directory__stat"><span class="enrollments-directory__stat-icon"><i class="fas fa-ban" aria-hidden="true"></i></span><span class="enrollments-directory__stat-copy"><span>Anulados</span><strong>{{ number_format($stats['cancelled']) }}</strong></span></div>
    </div>

    <form method="GET" action="{{ route('admin.enrollments.index') }}" class="enrollments-directory__filters" role="search">
      <label class="sr-only" for="enrollmentSearch">Buscar por alumno, DNI o código</label>
      <input id="enrollmentSearch" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Alumno, DNI o código de matrícula" />
      <label class="sr-only" for="enrollmentYearFilter">Año lectivo</label>
      <input id="enrollmentYearFilter" type="number" name="academic_year" min="2000" max="2100" value="{{ $academicYear }}" aria-label="Año lectivo" />
      <label class="sr-only" for="enrollmentGradeFilter">Grado</label>
      <select id="enrollmentGradeFilter" name="grade">
        <option value="">Todos los grados</option>
        @foreach(\App\Models\CourseAssignment::GRADES as $availableGrade)
          <option value="{{ $availableGrade }}" @selected($grade === $availableGrade)>{{ $availableGrade }}</option>
        @endforeach
      </select>
      <label class="sr-only" for="enrollmentStatusFilter">Estado</label>
      <select id="enrollmentStatusFilter" name="status">
        <option value="">Todos los estados</option>
        @foreach(\App\Models\Enrollment::STATUSES as $statusValue => $statusLabel)
          <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabel }}</option>
        @endforeach
      </select>
      <button type="submit"><i class="fas fa-search" aria-hidden="true"></i> Filtrar</button>
      @if($search !== '' || $grade !== '' || $status !== '' || $academicYear !== now()->year)
        <a href="{{ route('admin.enrollments.index') }}" class="enrollments-directory__clear">Limpiar</a>
      @endif
    </form>

    <div class="enrollments-directory__table-wrap">
      @if($enrollments->isEmpty())
        <div class="enrollments-directory__empty">
          <span class="enrollments-directory__empty-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></span>
          <strong>No hay matrículas para {{ $academicYear }}</strong>
          <p>Registra una matrícula o selecciona otro año lectivo para consultar su historial.</p>
          <a href="{{ route('admin.enrollments.create', ['academic_year' => $academicYear]) }}">Crear matrícula</a>
        </div>
      @else
        <table class="enrollments-directory__table">
          <thead>
            <tr><th>Ficha</th><th>Alumno</th><th>Año</th><th>Grado / sección</th><th>Apoderado</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr>
          </thead>
          <tbody>
            @foreach($enrollments as $enrollment)
              <tr>
                <td><span class="enrollments-directory__code"><strong>{{ $enrollment->enrollment_code }}</strong><span>{{ $enrollment->enrollment_date->format('d/m/Y') }}</span></span></td>
                <td><span class="enrollments-directory__student"><strong>{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</strong><span>DNI {{ $enrollment->student->dni }}</span></span></td>
                <td>{{ $enrollment->academic_year }}</td>
                <td>{{ $enrollment->grade }}{{ $enrollment->section ? ' · Sección '.$enrollment->section : '' }}</td>
                <td>{{ $enrollment->guardian_name }}</td>
                <td><span class="enrollments-directory__status enrollments-directory__status--{{ $enrollment->status }}"><i class="fas {{ $enrollment->status === 'enrolled' ? 'fa-circle-check' : ($enrollment->status === 'cancelled' ? 'fa-ban' : 'fa-hourglass-half') }}" aria-hidden="true"></i>{{ \App\Models\Enrollment::STATUSES[$enrollment->status] }}</span></td>
                <td>
                  <div class="enrollments-directory__actions">
                    <a href="{{ route('admin.enrollments.ficha', $enrollment->id) }}" class="enrollments-directory__action"><i class="fas fa-print" aria-hidden="true"></i> Ficha</a>
                    @if($enrollment->status !== 'cancelled')
                      <a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" class="enrollments-directory__action"><i class="fas fa-pen" aria-hidden="true"></i> Editar</a>
                      @if($enrollment->status === 'draft')
                        <form method="POST" action="{{ route('admin.enrollments.status', $enrollment->id) }}" onsubmit="return confirm('¿Confirmar esta matrícula?')">
                          @csrf
                          @method('PATCH')
                          <input type="hidden" name="status" value="enrolled" />
                          <button type="submit" class="enrollments-directory__action enrollments-directory__action--confirm"><i class="fas fa-check" aria-hidden="true"></i> Confirmar</button>
                        </form>
                      @endif
                      <form method="POST" action="{{ route('admin.enrollments.status', $enrollment->id) }}" onsubmit="return confirm('¿Anular esta matrícula? El registro permanecerá en el historial.')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled" />
                        <button type="submit" class="enrollments-directory__action enrollments-directory__action--cancel"><i class="fas fa-ban" aria-hidden="true"></i> Anular</button>
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

    <p class="enrollments-directory__results" role="status">Mostrando {{ $enrollments->firstItem() ?? 0 }}–{{ $enrollments->lastItem() ?? 0 }} de {{ $enrollments->total() }} matrículas del año {{ $academicYear }}.</p>
    @if($enrollments->hasPages())
      <div class="admin-pagination">{{ $enrollments->links() }}</div>
    @endif
  </section>
@endsection
