@extends('layouts.admin')

@php($adminSection = 'enrollments')

@push('styles')
  <style>
    .enrollment-sheet {
      max-width: 920px;
      margin: 0 auto;
      padding: 1.25rem !important;
      border: 1px solid #e2e9e3 !important;
      border-radius: 18px !important;
      background: #f6f8f6 !important;
      box-shadow: 0 15px 38px rgba(24, 56, 36, .07) !important;
      color: #273b2d;
    }

    .enrollment-sheet__toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: .75rem;
      margin-bottom: .9rem;
    }

    .enrollment-sheet__toolbar a,
    .enrollment-sheet__toolbar button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      min-height: 38px;
      padding: .55rem .75rem;
      border: 1px solid #dce6dd;
      border-radius: 9px;
      background: #fff;
      color: #42614a;
      font: inherit;
      font-size: .72rem;
      font-weight: 750;
      text-decoration: none;
      cursor: pointer;
    }

    .enrollment-sheet__toolbar button {
      border-color: #17683e;
      background: #17683e;
      color: #fff;
    }

    .enrollment-sheet__paper {
      padding: clamp(1.25rem, 4vw, 2.5rem);
      border: 1px solid #e5e9e5;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 4px 15px rgba(24, 56, 36, .04);
    }

    .enrollment-sheet__heading {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 1rem;
      align-items: start;
      padding-bottom: 1.15rem;
      border-bottom: 2px solid #345d40;
    }

    .enrollment-sheet__school {
      color: #5e7864;
      font-size: .65rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    .enrollment-sheet__heading h1 {
      margin: .3rem 0 .22rem;
      color: #183b29;
      font-family: Georgia, 'Times New Roman', serif;
      font-size: clamp(1.35rem, 3vw, 1.8rem);
      letter-spacing: -.025em;
    }

    .enrollment-sheet__heading p {
      margin: 0;
      color: #6a786d;
      font-size: .72rem;
    }

    .enrollment-sheet__badge {
      min-width: 135px;
      padding: .62rem .7rem;
      border: 1px solid #dce7dd;
      border-radius: 10px;
      background: #f7faf7;
      text-align: center;
    }

    .enrollment-sheet__badge span,
    .enrollment-sheet__badge strong {
      display: block;
    }

    .enrollment-sheet__badge span {
      color: #718074;
      font-size: .59rem;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
    }

    .enrollment-sheet__badge strong {
      margin-top: .22rem;
      color: #294c33;
      font-size: .8rem;
    }

    .enrollment-sheet__status {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      margin-top: .5rem;
      padding: .23rem .48rem;
      border-radius: 999px;
      background: #f1f5ef;
      color: #5d6e5e;
      font-size: .62rem;
      font-weight: 750;
    }

    .enrollment-sheet__status--enrolled {
      background: #eaf5ec;
      color: #28633a;
    }

    .enrollment-sheet__status--cancelled {
      background: #fff0ed;
      color: #a24a42;
    }

    .enrollment-sheet__section {
      margin-top: 1.2rem;
    }

    .enrollment-sheet__section h2 {
      display: flex;
      align-items: center;
      gap: .48rem;
      margin: 0 0 .62rem;
      padding-bottom: .4rem;
      border-bottom: 1px solid #e6ece6;
      color: #31583b;
      font-size: .75rem;
      font-weight: 800;
    }

    .enrollment-sheet__section h2 i {
      color: #66836b;
    }

    .enrollment-sheet__grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: .75rem 1rem;
    }

    .enrollment-sheet__field {
      min-width: 0;
    }

    .enrollment-sheet__field--wide {
      grid-column: 1 / -1;
    }

    .enrollment-sheet__field span {
      display: block;
      margin-bottom: .17rem;
      color: #79857b;
      font-size: .59rem;
      font-weight: 750;
      letter-spacing: .035em;
      text-transform: uppercase;
    }

    .enrollment-sheet__field strong {
      display: block;
      min-height: 1.25rem;
      color: #2f3e32;
      font-size: .75rem;
      font-weight: 650;
      line-height: 1.45;
      overflow-wrap: anywhere;
      white-space: pre-wrap;
    }

    .enrollment-sheet__note {
      margin-top: 1.25rem;
      padding: .65rem .75rem;
      border-left: 3px solid #b6cbb8;
      background: #f7faf7;
      color: #69776b;
      font-size: .64rem;
      line-height: 1.5;
    }

    .enrollment-sheet__signatures {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2.4rem;
      margin-top: 3rem;
    }

    .enrollment-sheet__signature {
      padding-top: .45rem;
      border-top: 1px solid #718075;
      color: #4f5f52;
      font-size: .65rem;
      text-align: center;
    }

    @media (max-width: 620px) {
      .enrollment-sheet {
        padding: .7rem !important;
      }

      .enrollment-sheet__heading {
        grid-template-columns: 1fr;
      }

      .enrollment-sheet__badge {
        text-align: left;
      }

      .enrollment-sheet__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media print {
      @page {
        size: A4 portrait;
        margin: 15mm;
      }

      body,
      .admin-area,
      .admin-content,
      .admin-body {
        background: #fff !important;
      }

      .admin-area {
        min-height: 0 !important;
        padding: 0 !important;
      }

      .admin-layout {
        display: block !important;
      }

      .admin-sidebar,
      .admin-sidebar-mobile-trigger,
      .admin-sidebar-backdrop,
      .admin-page-header,
      .enrollment-sheet__toolbar {
        display: none !important;
      }

      .admin-content,
      .admin-content .container,
      .admin-body {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
      }

      .enrollment-sheet,
      .enrollment-sheet__paper {
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: #fff !important;
      }

      .enrollment-sheet__paper {
        padding: 0 !important;
      }

      .enrollment-sheet__section,
      .enrollment-sheet__signatures,
      .enrollment-sheet__note {
        break-inside: avoid;
      }

      .enrollment-sheet__badge,
      .enrollment-sheet__status,
      .enrollment-sheet__note {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
      }
    }
  </style>
@endpush

@section('admin_content')
  <main class="admin-card enrollment-sheet">
    <div class="enrollment-sheet__toolbar">
      <a href="{{ route('admin.enrollments.index', ['academic_year' => $enrollment->academic_year]) }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a matrículas</a>
      <button type="button" onclick="window.print()"><i class="fas fa-print" aria-hidden="true"></i> Imprimir ficha</button>
    </div>

    <article class="enrollment-sheet__paper">
      <header class="enrollment-sheet__heading">
        <div>
          <span class="enrollment-sheet__school">Registro académico</span>
          <h1>Ficha de matrícula</h1>
          <p>Registro escolar correspondiente al año lectivo {{ $enrollment->academic_year }}</p>
        </div>
        <div class="enrollment-sheet__badge">
          <span>Código de matrícula</span>
          <strong>{{ $enrollment->enrollment_code ?: 'Pendiente' }}</strong>
          <span class="enrollment-sheet__status enrollment-sheet__status--{{ $enrollment->status }}">
            <i class="fas {{ $enrollment->status === 'enrolled' ? 'fa-circle-check' : ($enrollment->status === 'cancelled' ? 'fa-ban' : 'fa-hourglass-half') }}" aria-hidden="true"></i>
            {{ \App\Models\Enrollment::STATUSES[$enrollment->status] ?? $enrollment->status }}
          </span>
        </div>
      </header>

      <section class="enrollment-sheet__section" aria-labelledby="enrollmentStudentHeading">
        <h2 id="enrollmentStudentHeading"><i class="fas fa-user-graduate" aria-hidden="true"></i> Datos del estudiante</h2>
        <div class="enrollment-sheet__grid">
          <div class="enrollment-sheet__field enrollment-sheet__field--wide"><span>Apellidos y nombres</span><strong>{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</strong></div>
          <div class="enrollment-sheet__field"><span>DNI / documento</span><strong>{{ $enrollment->student->dni }}</strong></div>
          <div class="enrollment-sheet__field"><span>Fecha de nacimiento</span><strong>{{ $enrollment->student->birth_date?->format('d/m/Y') ?: 'No registrada' }}</strong></div>
          <div class="enrollment-sheet__field"><span>Cuenta de acceso</span><strong>{{ $enrollment->student->user?->email ?: 'No vinculada' }}</strong></div>
        </div>
      </section>

      <section class="enrollment-sheet__section" aria-labelledby="enrollmentAcademicHeading">
        <h2 id="enrollmentAcademicHeading"><i class="fas fa-book-open" aria-hidden="true"></i> Datos de matrícula</h2>
        <div class="enrollment-sheet__grid">
          <div class="enrollment-sheet__field"><span>Año lectivo</span><strong>{{ $enrollment->academic_year }}</strong></div>
          <div class="enrollment-sheet__field"><span>Grado</span><strong>{{ $enrollment->grade }}</strong></div>
          <div class="enrollment-sheet__field"><span>Sección</span><strong>{{ $enrollment->section ?: 'No especificada' }}</strong></div>
          <div class="enrollment-sheet__field"><span>Fecha de registro</span><strong>{{ $enrollment->enrollment_date->format('d/m/Y') }}</strong></div>
          <div class="enrollment-sheet__field"><span>Fecha de emisión</span><strong>{{ now()->format('d/m/Y') }}</strong></div>
          @if($enrollment->cancelled_at)
            <div class="enrollment-sheet__field"><span>Fecha de anulación</span><strong>{{ $enrollment->cancelled_at->format('d/m/Y H:i') }}</strong></div>
          @endif
        </div>
      </section>

      <section class="enrollment-sheet__section" aria-labelledby="enrollmentGuardianHeading">
        <h2 id="enrollmentGuardianHeading"><i class="fas fa-user-shield" aria-hidden="true"></i> Apoderado y contacto</h2>
        <div class="enrollment-sheet__grid">
          <div class="enrollment-sheet__field enrollment-sheet__field--wide"><span>Apellidos y nombres</span><strong>{{ $enrollment->guardian_name }}</strong></div>
          <div class="enrollment-sheet__field"><span>Parentesco</span><strong>{{ $enrollment->guardian_relationship }}</strong></div>
          <div class="enrollment-sheet__field"><span>Teléfono</span><strong>{{ $enrollment->guardian_phone ?: 'No registrado' }}</strong></div>
          <div class="enrollment-sheet__field"><span>Correo electrónico</span><strong>{{ $enrollment->guardian_email ?: 'No registrado' }}</strong></div>
          <div class="enrollment-sheet__field enrollment-sheet__field--wide"><span>Dirección</span><strong>{{ $enrollment->address ?: 'No registrada' }}</strong></div>
        </div>
      </section>

      @if($enrollment->notes)
        <section class="enrollment-sheet__section" aria-labelledby="enrollmentNotesHeading">
          <h2 id="enrollmentNotesHeading"><i class="fas fa-note-sticky" aria-hidden="true"></i> Observaciones administrativas</h2>
          <div class="enrollment-sheet__field"><strong>{{ $enrollment->notes }}</strong></div>
        </section>
      @endif

      <p class="enrollment-sheet__note">Documento generado por el sistema para consulta y archivo institucional. Verifique que los datos sean correctos y complete las firmas requeridas por la institución. Esta ficha no reemplaza los formatos oficiales que correspondan.</p>

      <div class="enrollment-sheet__signatures">
        <div class="enrollment-sheet__signature">Firma del apoderado</div>
        <div class="enrollment-sheet__signature">Firma y sello de la institución</div>
      </div>
    </article>
  </main>
@endsection
