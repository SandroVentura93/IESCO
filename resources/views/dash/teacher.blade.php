@extends('layouts.app')

@push('styles')
  <style>
    .teacher-dashboard {
      width: 100%;
      max-width: none;
      min-height: 100vh;
      margin: 0;
      padding: calc(var(--nav-h) + 1.5rem) 0 4rem;
      background:
        radial-gradient(circle at 88% 0, rgba(214, 185, 108, .1), transparent 28rem),
        #f4f7f4;
    }

    .teacher-dashboard__container {
      width: 100%;
      max-width: none;
      margin: 0;
      padding-right: clamp(1rem, 2vw, 2rem);
      box-sizing: border-box;
    }

    .teacher-dashboard__workspace {
      display: grid;
      grid-template-columns: 280px minmax(0, 1fr);
      align-items: start;
      gap: 1.5rem;
    }

    .teacher-dashboard__main {
      display: grid;
      gap: 1.15rem;
      min-width: 0;
    }

    .teacher-dashboard__hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1.25rem;
      min-height: 180px;
      padding: clamp(1.4rem, 3vw, 2.25rem);
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, .16);
      border-radius: 20px;
      background:
        radial-gradient(ellipse at 90% 10%, rgba(214, 185, 108, .28), transparent 38%),
        linear-gradient(125deg, #104d32, #17683e 64%, #26784d);
      box-shadow: 0 18px 42px rgba(17, 76, 49, .18);
      color: #fff;
    }

    .teacher-dashboard__eyebrow,
    .teacher-dashboard__section-kicker {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      color: #d8e9dc;
      font-size: .76rem;
      font-weight: 800;
      letter-spacing: .09em;
      text-transform: uppercase;
    }

    .teacher-dashboard__hero h1 {
      margin: .7rem 0 .55rem;
      color: #fff;
      font-size: clamp(1.85rem, 4vw, 2.65rem);
      letter-spacing: -.035em;
    }

    .teacher-dashboard__hero p {
      max-width: 620px;
      color: rgba(255, 255, 255, .82);
      font-size: .98rem;
    }

    .teacher-dashboard__hero-action {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .65rem;
      min-height: 48px;
      padding: .8rem 1.05rem;
      border: 1px solid rgba(255, 255, 255, .3);
      border-radius: 12px;
      background: #fff;
      color: #155b38;
      font-size: .9rem;
      font-weight: 800;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .teacher-dashboard__hero-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(0, 0, 0, .16);
    }

    .teacher-dashboard__metrics {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: .9rem;
    }

    .teacher-dashboard__metric,
    .teacher-dashboard__panel,
    .teacher-dashboard__course-card,
    .teacher-dashboard__resource,
    .teacher-dashboard__permissions {
      border: 1px solid #e7ede7;
      border-radius: 16px;
      background: #fff;
      box-shadow: 0 7px 22px rgba(19, 56, 36, .045);
    }

    .teacher-dashboard__permissions {
      display: grid;
      grid-template-columns: auto 1fr;
      align-items: start;
      gap: .25rem .8rem;
      margin: 0;
      padding: .9rem 1.1rem;
      border-color: #d7e8da;
      background: #f7fbf7;
      box-shadow: none;
    }

    .teacher-dashboard__permissions i {
      grid-row: span 2;
      padding-top: .15rem;
      color: #276644;
    }

    .teacher-dashboard__permissions strong {
      color: #193f2d;
      font-size: .9rem;
    }

    .teacher-dashboard__permissions p {
      margin: 0;
      color: #5f7064;
      font-size: .84rem;
      line-height: 1.55;
    }

    .teacher-dashboard__metric {
      display: flex;
      align-items: center;
      gap: 1rem;
      min-height: 102px;
      padding: 1rem 1.1rem;
    }

    .teacher-dashboard__metric-icon,
    .teacher-dashboard__course-icon,
    .teacher-dashboard__resource-icon {
      display: grid;
      flex: 0 0 auto;
      place-items: center;
      width: 46px;
      height: 46px;
      border-radius: 13px;
      background: #edf6ef;
      color: #17683e;
      font-size: 1.15rem;
    }

    .teacher-dashboard__metric-label,
    .teacher-dashboard__metric-note {
      display: block;
      color: #66756a;
      font-size: .82rem;
    }

    .teacher-dashboard__metric-value {
      display: block;
      margin: .08rem 0;
      color: #123f2b;
      font-family: var(--font-serif);
      font-size: 1.65rem;
      font-weight: 800;
      line-height: 1.15;
    }

    .teacher-dashboard__content {
      display: grid;
      grid-template-columns: minmax(0, 1.25fr) minmax(0, .82fr);
      align-items: start;
      gap: 1.1rem;
    }

    .teacher-dashboard__page-panel {
      width: min(100%, 1050px);
    }

    .teacher-dashboard__page-panel--narrow {
      width: min(100%, 760px);
    }

    .teacher-dashboard__main-column,
    .teacher-dashboard__side-column {
      display: grid;
      gap: 1rem;
      min-width: 0;
    }

    .teacher-dashboard__sidebar {
      position: sticky;
      top: calc(var(--nav-h) + 1.25rem);
      display: flex;
      min-width: 0;
      flex-direction: column;
      justify-content: space-between;
      gap: 2rem;
      height: calc(100vh - var(--nav-h) - 3rem);
      min-height: max(540px, calc(100vh - var(--nav-h) - 3rem));
      padding: 1.35rem 1rem;
      box-sizing: border-box;
      border: 1px solid rgba(255, 255, 255, .12);
      border-radius: 0 18px 18px 0;
      background:
        radial-gradient(circle at 100% 0, rgba(214, 185, 108, .13), transparent 13rem),
        linear-gradient(165deg, #123f2b, #0d3524 76%);
      box-shadow: 0 18px 42px rgba(17, 76, 49, .18);
      color: #fff;
    }

    .teacher-dashboard__sidebar-brand {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .2rem .35rem 1.15rem;
      border-bottom: 1px solid rgba(255, 255, 255, .14);
    }

    .teacher-dashboard__sidebar-top {
      display: grid;
      align-content: start;
      gap: 1.35rem;
      min-width: 0;
    }

    .teacher-dashboard__sidebar-top > div {
      min-width: 0;
    }

    .teacher-dashboard__sidebar-brand-icon {
      display: grid;
      flex: 0 0 auto;
      width: 42px;
      height: 42px;
      place-items: center;
      border: 1px solid rgba(255, 255, 255, .2);
      border-radius: 13px;
      background: rgba(255, 255, 255, .1);
      color: #f3d98c;
    }

    .teacher-dashboard__sidebar-brand strong,
    .teacher-dashboard__sidebar-brand span {
      display: block;
    }

    .teacher-dashboard__sidebar-brand strong {
      color: #fff;
      font-size: .9rem;
    }

    .teacher-dashboard__sidebar-brand span {
      margin-top: .15rem;
      color: rgba(255, 255, 255, .66);
      font-size: .7rem;
    }

    .teacher-dashboard__sidebar-group-label {
      display: block;
      margin: 0 .55rem .45rem;
      color: rgba(255, 255, 255, .58);
      font-size: .65rem;
      font-weight: 800;
      letter-spacing: .12em;
      text-transform: uppercase;
    }

    .teacher-dashboard__sidebar-title {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }

    .teacher-dashboard__sidebar-nav {
      display: grid;
      gap: .65rem;
    }

    .teacher-dashboard__sidebar-link {
      display: flex;
      align-items: center;
      gap: .7rem;
      min-height: 50px;
      padding: .55rem .65rem;
      border: 1px solid rgba(255, 255, 255, .14);
      border-radius: 11px;
      background: rgba(255, 255, 255, .055);
      box-shadow: 0 4px 12px rgba(4, 25, 16, .1);
      color: rgba(255, 255, 255, .86);
      font-size: .84rem;
      font-weight: 700;
      line-height: 1.25;
      transition: transform .2s ease, background .2s ease, border-color .2s ease, box-shadow .2s ease, color .2s ease;
    }

    .teacher-dashboard__sidebar-link i {
      display: grid;
      flex: 0 0 34px;
      width: 34px;
      height: 34px;
      place-items: center;
      border: 1px solid rgba(255, 255, 255, .16);
      border-radius: 9px;
      background: rgba(255, 255, 255, .11);
      color: #e1eee4;
      text-align: center;
    }

    .teacher-dashboard__sidebar-link:hover {
      transform: translateX(3px);
      border-color: rgba(243, 217, 140, .4);
      background: rgba(255, 255, 255, .12);
      box-shadow: 0 7px 16px rgba(4, 25, 16, .16);
      color: #fff;
    }

    .teacher-dashboard__sidebar-link[aria-current="location"] {
      border-color: rgba(243, 217, 140, .52);
      background: linear-gradient(110deg, rgba(243, 217, 140, .2), rgba(255, 255, 255, .1));
      box-shadow: inset 3px 0 #f3d98c, 0 7px 16px rgba(4, 25, 16, .14);
      color: #fff;
    }

    .teacher-dashboard__sidebar-link[aria-current="location"] i {
      border-color: rgba(243, 217, 140, .48);
      background: rgba(243, 217, 140, .2);
      color: #f3d98c;
    }

    .teacher-dashboard__sidebar-link:focus-visible {
      outline: 3px solid #f3d98c;
      outline-offset: 2px;
    }

    .teacher-dashboard__sidebar-link span {
      flex: 1;
    }

    .teacher-dashboard__sidebar-note {
      display: grid;
      gap: .35rem;
      padding: .85rem;
      border: 1px solid rgba(255, 255, 255, .12);
      border-radius: 12px;
      background: rgba(255, 255, 255, .07);
    }

    .teacher-dashboard__sidebar-note strong {
      color: #fff;
      font-size: .76rem;
    }

    .teacher-dashboard__sidebar-note span {
      color: rgba(255, 255, 255, .7);
      font-size: .7rem;
      line-height: 1.5;
    }

    .teacher-dashboard__panel {
      padding: clamp(1.15rem, 3vw, 1.55rem);
    }

    .teacher-dashboard__panel-heading {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.1rem;
    }

    .teacher-dashboard__section-kicker {
      color: #65816d;
      font-size: .7rem;
    }

    .teacher-dashboard__panel-heading h2 {
      margin: .2rem 0 0;
      color: #173f2b;
      font-size: 1.3rem;
    }

    .teacher-dashboard__panel-heading p,
    .teacher-dashboard__empty p {
      margin-top: .3rem;
      color: #69776d;
      font-size: .88rem;
    }

    .teacher-dashboard__course-list,
    .teacher-dashboard__resource-list {
      display: grid;
      gap: .75rem;
    }

    .teacher-dashboard__course-card {
      display: flex;
      align-items: center;
      gap: .9rem;
      padding: 1rem;
      box-shadow: none;
      transition: border-color .2s ease, transform .2s ease;
    }

    .teacher-dashboard__course-card:hover {
      transform: translateY(-1px);
      border-color: #b5d2bc;
    }

    .teacher-dashboard__course-copy {
      flex: 1;
      min-width: 0;
    }

    .teacher-dashboard__course-copy strong,
    .teacher-dashboard__course-copy span {
      display: block;
    }

    .teacher-dashboard__course-copy strong {
      color: #193f2d;
      font-size: .95rem;
    }

    .teacher-dashboard__course-copy span {
      margin-top: .15rem;
      color: #6c786e;
      font-size: .82rem;
    }

    .teacher-dashboard__course-count {
      flex: 0 0 auto;
      padding: .35rem .65rem;
      border-radius: 999px;
      background: #f0f6f1;
      color: #276644;
      font-size: .74rem;
      font-weight: 800;
    }

    .teacher-dashboard__resource {
      display: flex;
      align-items: center;
      gap: .8rem;
      padding: .85rem;
      box-shadow: none;
    }

    .teacher-dashboard__resource-icon {
      width: 40px;
      height: 40px;
      border-radius: 11px;
      font-size: 1rem;
    }

    .teacher-dashboard__resource-copy {
      flex: 1;
      min-width: 0;
    }

    .teacher-dashboard__resource-copy a {
      display: block;
      overflow: hidden;
      color: #193f2d;
      font-size: .88rem;
      font-weight: 800;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .teacher-dashboard__resource-copy span,
    .teacher-dashboard__resource-date {
      display: block;
      margin-top: .12rem;
      color: #6c786e;
      font-size: .75rem;
    }

    .teacher-dashboard__resource-details {
      margin-top: .55rem;
      padding-top: .45rem;
      border-top: 1px solid #edf1ed;
    }

    .teacher-dashboard__resource-details summary {
      width: fit-content;
      color: #276644;
      cursor: pointer;
      font-size: .76rem;
      font-weight: 800;
    }

    .teacher-dashboard__resource-metadata {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .5rem .8rem;
      margin: .65rem 0 0;
    }

    .teacher-dashboard__resource-metadata div {
      min-width: 0;
    }

    .teacher-dashboard__resource-metadata dt {
      color: #69776d;
      font-size: .68rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .teacher-dashboard__resource-metadata dd {
      margin: .1rem 0 0;
      overflow-wrap: anywhere;
      color: #263b2e;
      font-size: .76rem;
      line-height: 1.45;
    }

    .teacher-dashboard__resource-date {
      flex: 0 0 auto;
      margin: 0;
      white-space: nowrap;
    }

    .teacher-dashboard__resource-action {
      flex: 0 0 auto;
    }

    .teacher-dashboard__resource-action .btn {
      min-height: 36px;
      white-space: nowrap;
    }

    @media (max-width: 720px) {
      .teacher-dashboard__resource {
        flex-wrap: wrap;
      }

      .teacher-dashboard__resource-copy {
        flex-basis: calc(100% - 56px);
      }

      .teacher-dashboard__resource-metadata {
        grid-template-columns: 1fr;
      }

      .teacher-dashboard__resource-action {
        margin-left: auto;
      }
    }

    .teacher-dashboard__upload-form {
      display: grid;
      gap: .95rem;
    }

    .teacher-dashboard__field {
      display: grid;
      gap: .4rem;
    }

    .teacher-dashboard__field[hidden] {
      display: none;
    }

    .teacher-dashboard__field label {
      color: #334b3a;
      font-size: .82rem;
      font-weight: 800;
    }

    .teacher-dashboard__field input,
    .teacher-dashboard__field select {
      width: 100%;
      min-height: 46px;
      padding: .65rem .75rem;
      border: 1px solid #d9e3da;
      border-radius: 10px;
      background: #fff;
      color: #243a2b;
      font: inherit;
      font-size: .88rem;
    }

    .teacher-dashboard__field input[type="file"] {
      padding: .45rem;
      font-size: .77rem;
    }

    .teacher-dashboard__field input[type="file"]::file-selector-button {
      margin-right: .5rem;
      padding: .45rem .6rem;
      border: 0;
      border-radius: 7px;
      background: #edf6ef;
      color: #17683e;
      font: inherit;
      font-weight: 800;
    }

    .teacher-dashboard__field input:focus,
    .teacher-dashboard__field select:focus {
      outline: 3px solid rgba(23, 104, 62, .15);
      border-color: #17683e;
    }

    .teacher-dashboard__form-hint {
      margin-top: -.45rem;
      color: #708074;
      font-size: .74rem;
    }

    .teacher-dashboard__submit {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .55rem;
      min-height: 46px;
      padding: .7rem 1rem;
      border-radius: 10px;
      background: #17683e;
      color: #fff;
      font-size: .88rem;
      font-weight: 800;
      transition: background .2s ease, transform .2s ease;
    }

    .teacher-dashboard__submit:hover:not(:disabled) {
      transform: translateY(-1px);
      background: #10532f;
    }

    .teacher-dashboard__submit:disabled {
      cursor: not-allowed;
      opacity: .55;
    }

    .teacher-dashboard__alert {
      margin-bottom: 1rem;
      padding: .9rem 1rem;
      border: 1px solid #b8e2c3;
      border-radius: 12px;
      background: #eef9f0;
      color: #176339;
      font-size: .9rem;
    }

    .teacher-dashboard__alert--error {
      border-color: #f0c4c4;
      background: #fff4f2;
      color: #8d2520;
    }

    .teacher-dashboard__empty {
      padding: 1.2rem;
      border: 1px dashed #d7e2d9;
      border-radius: 12px;
      background: #fbfdfb;
      text-align: center;
    }

    .teacher-dashboard__empty i {
      color: #528265;
      font-size: 1.35rem;
    }

    .teacher-dashboard__empty strong {
      display: block;
      margin-top: .45rem;
      color: #244333;
    }

    @media (max-width: 1120px) {
      .teacher-dashboard__workspace {
        grid-template-columns: 230px minmax(0, 1fr);
        gap: 1rem;
      }

      .teacher-dashboard__content {
        grid-template-columns: minmax(0, 1fr);
      }
    }

    @media (max-width: 820px) {
      .teacher-dashboard__container {
        width: min(100% - 2rem, 1180px);
        margin-inline: auto;
      }

      .teacher-dashboard__workspace {
        grid-template-columns: minmax(0, 1fr);
      }

      .teacher-dashboard__sidebar {
        position: sticky;
        z-index: 5;
        top: calc(var(--nav-h) + .5rem);
        height: auto;
        min-height: 0;
        gap: .75rem;
        padding: .8rem;
        border-radius: 18px;
      }

      .teacher-dashboard__sidebar-brand {
        display: none;
      }

      .teacher-dashboard__sidebar-group-label,
      .teacher-dashboard__sidebar-note {
        display: none;
      }

      .teacher-dashboard__sidebar-nav {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
      }

      .teacher-dashboard__sidebar-link {
        min-width: 0;
        min-height: 42px;
        padding: .6rem .75rem;
      }

      .teacher-dashboard__sidebar-link i {
        flex-basis: 30px;
        width: 30px;
        height: 30px;
      }
    }

    @media (max-width: 620px) {
      .teacher-dashboard {
        padding-top: calc(var(--nav-h) + 1.1rem);
      }

      .teacher-dashboard__container {
        width: min(100% - 1.1rem, 1180px);
      }

      .teacher-dashboard__hero {
        align-items: flex-start;
        flex-direction: column;
        min-height: auto;
        border-radius: 16px;
      }

      .teacher-dashboard__hero-action {
        width: 100%;
      }

      .teacher-dashboard__metrics {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .45rem;
      }

      .teacher-dashboard__metric {
        align-items: flex-start;
        flex-direction: column;
        gap: .55rem;
        min-height: 116px;
        padding: .75rem;
      }

      .teacher-dashboard__metric-icon {
        width: 36px;
        height: 36px;
        font-size: .95rem;
      }

      .teacher-dashboard__metric-value {
        font-size: 1.35rem;
      }

      .teacher-dashboard__metric-label,
      .teacher-dashboard__metric-note {
        font-size: .7rem;
      }

      .teacher-dashboard__permissions {
        padding: .85rem;
      }

      .teacher-dashboard__panel-heading {
        flex-direction: column;
      }

      .teacher-dashboard__sidebar {
        padding: .6rem;
      }

      .teacher-dashboard__sidebar-link {
        min-height: 40px;
        padding: .55rem .65rem;
        font-size: .75rem;
      }

      .teacher-dashboard__sidebar-link i {
        flex-basis: 28px;
        width: 28px;
        height: 28px;
      }

      .teacher-dashboard__resource {
        align-items: flex-start;
        flex-wrap: wrap;
      }

      .teacher-dashboard__resource-copy {
        flex-basis: calc(100% - 56px);
      }

      .teacher-dashboard__resource-action {
        margin-left: auto;
      }

      .teacher-dashboard__resource-date {
        display: none;
      }
    }
  </style>
@endpush

@section('page_title', match ($activePage) {
  'courses' => 'Mis cursos — Panel docente',
  'resources' => 'Mis recursos — Panel docente',
  'upload' => 'Subir material — Panel docente',
  default => 'Panel docente',
})
@section('full_width', true)

@section('content')
  <section class="section teacher-dashboard" aria-labelledby="teacherDashboardTitle">
    <div class="teacher-dashboard__container">
      @if(session('success'))
        <div class="teacher-dashboard__alert" role="status">{{ session('success') }}</div>
      @endif

      @if($errors->any())
        <div class="teacher-dashboard__alert teacher-dashboard__alert--error" role="alert">
          <strong>No se pudo completar la operación.</strong>
          <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="teacher-dashboard__workspace">
        <aside class="teacher-dashboard__sidebar" aria-label="Menú docente">
          <div class="teacher-dashboard__sidebar-top">
            <div class="teacher-dashboard__sidebar-brand">
              <span class="teacher-dashboard__sidebar-brand-icon"><i class="fas fa-chalkboard-user" aria-hidden="true"></i></span>
              <div>
                <strong>Panel docente</strong>
                <span>Espacio de enseñanza</span>
              </div>
            </div>
            <div>
              <span class="teacher-dashboard__sidebar-group-label">Tu espacio</span>
              <h2 class="teacher-dashboard__sidebar-title">Menú docente</h2>
              <nav class="teacher-dashboard__sidebar-nav" aria-label="Secciones del panel docente">
                <a class="teacher-dashboard__sidebar-link" href="{{ route('teacher.dashboard') }}" @if($activePage === 'overview') aria-current="page" @endif>
                  <i class="fas fa-chart-pie" aria-hidden="true"></i><span>Resumen</span>
                </a>
                <a class="teacher-dashboard__sidebar-link" href="{{ route('teacher.courses') }}" @if($activePage === 'courses') aria-current="page" @endif>
                  <i class="fas fa-book-open" aria-hidden="true"></i><span>Mis cursos</span>
                </a>
                <a class="teacher-dashboard__sidebar-link" href="{{ route('teacher.resources.index') }}" @if($activePage === 'resources') aria-current="page" @endif>
                  <i class="fas fa-folder-open" aria-hidden="true"></i><span>Mis recursos</span>
                </a>
                <a class="teacher-dashboard__sidebar-link" href="{{ route('teacher.resources.create') }}" aria-label="Subir recursos pedagógicos" @if($activePage === 'upload') aria-current="page" @endif>
                  <i class="fas fa-cloud-arrow-up" aria-hidden="true"></i><span>Subir material</span>
                </a>
              </nav>
            </div>
          </div>
          <div class="teacher-dashboard__sidebar-note">
            <strong><i class="fas fa-shield-halved" aria-hidden="true"></i> Acceso docente</strong>
            <span>Gestiona tus cursos asignados y tus propios recursos. El administrador controla usuarios, permisos y asignaciones.</span>
          </div>
        </aside>

        <div class="teacher-dashboard__main">
          <header class="teacher-dashboard__hero">
            <div>
              <span class="teacher-dashboard__eyebrow"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i>
                @if($activePage === 'overview')
                  Espacio docente
                @elseif($activePage === 'courses')
                  Tu enseñanza
                @elseif($activePage === 'resources')
                  Biblioteca de clase
                @else
                  Comparte con tu clase
                @endif
              </span>
              <h1 id="teacherDashboardTitle">
                @if($activePage === 'overview')
                  Hola, {{ $user->name }}
                @elseif($activePage === 'courses')
                  Mis cursos
                @elseif($activePage === 'resources')
                  Mis recursos
                @else
                  Subir material
                @endif
              </h1>
              <p>
                @if($activePage === 'overview')
                  Organiza tus cursos y comparte materiales de clase desde un solo lugar.
                @elseif($activePage === 'courses')
                  Consulta tus cursos asignados y el material disponible para cada uno.
                @elseif($activePage === 'resources')
                  Consulta la ubicación de tus archivos y administra los recursos que compartiste.
                @else
                  Organiza un recurso pedagógico en la carpeta de grado y curso que corresponda.
                @endif
              </p>
            </div>
            @if($activePage !== 'upload')
              <a class="teacher-dashboard__hero-action" href="{{ route('teacher.resources.create') }}">
                <i class="fas fa-cloud-arrow-up" aria-hidden="true"></i>
                <span>Subir material</span>
              </a>
            @endif
          </header>

          @if($activePage === 'overview')
            <section class="teacher-dashboard__metrics" aria-label="Resumen de tu actividad">
            <article class="teacher-dashboard__metric">
              <span class="teacher-dashboard__metric-icon"><i class="fas fa-book-open" aria-hidden="true"></i></span>
              <div>
                <span class="teacher-dashboard__metric-label">Mis cursos</span>
                <strong class="teacher-dashboard__metric-value">{{ $stats['courses'] }}</strong>
                <span class="teacher-dashboard__metric-note">Cursos asignados</span>
              </div>
            </article>
            <article class="teacher-dashboard__metric">
              <span class="teacher-dashboard__metric-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></span>
              <div>
                <span class="teacher-dashboard__metric-label">Mis recursos</span>
                <strong class="teacher-dashboard__metric-value">{{ $stats['resources'] }}</strong>
                <span class="teacher-dashboard__metric-note">Materiales compartidos</span>
              </div>
            </article>
            <article class="teacher-dashboard__metric">
              <span class="teacher-dashboard__metric-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
              <div>
                <span class="teacher-dashboard__metric-label">Este mes</span>
                <strong class="teacher-dashboard__metric-value">{{ $stats['monthly_resources'] }}</strong>
                <span class="teacher-dashboard__metric-note">Materiales publicados</span>
              </div>
            </article>
          </section>

          <aside class="teacher-dashboard__permissions" aria-label="Permisos y funciones docentes">
            <i class="fas fa-shield-halved" aria-hidden="true"></i>
            <strong>Funciones de tu cuenta docente</strong>
            <p>Consulta únicamente los cursos que te asignó el administrador; sube y elimina solo tus propios recursos pedagógicos. La asignación de cursos y la gestión de permisos se realizan desde los menús de administración.</p>
          </aside>
          @elseif($activePage === 'courses')
            <section class="teacher-dashboard__panel teacher-dashboard__page-panel" aria-labelledby="teacherDashboardTitle">
            @if($courses->isNotEmpty())
              <div class="teacher-dashboard__course-list">
                @foreach($courses as $course)
                  <article class="teacher-dashboard__course-card">
                    <span class="teacher-dashboard__course-icon"><i class="fas fa-book" aria-hidden="true"></i></span>
                    <div class="teacher-dashboard__course-copy">
                      <strong>{{ $course->name }}</strong>
                      <span>
                        Grado: {{ $course->pivot->grade ?: 'No especificado' }}
                        @if($course->pivot->section)
                          · Sección {{ $course->pivot->section }}
                        @endif
                      </span>
                    </div>
                    <span class="teacher-dashboard__course-count">
                      {{ $course->teacher_resources_count }} {{ $course->teacher_resources_count === 1 ? 'recurso' : 'recursos' }}
                    </span>
                  </article>
                @endforeach
              </div>
            @else
              <div class="teacher-dashboard__empty">
                <i class="fas fa-book-open" aria-hidden="true"></i>
                <strong>Aún no tienes cursos asignados</strong>
                <p>Solicita al administrador que vincule tus cursos para poder compartir materiales.</p>
              </div>
            @endif
          </section>
          @elseif($activePage === 'resources')
            <section class="teacher-dashboard__panel teacher-dashboard__page-panel" aria-labelledby="teacherDashboardTitle">
            @if($resources->isNotEmpty() || $otherFiles->isNotEmpty())
              @if($resources->isNotEmpty())
                <div class="teacher-dashboard__resource-list">
                  @foreach($resources as $resource)
                    <article class="teacher-dashboard__resource">
                      <span class="teacher-dashboard__resource-icon"><i class="fas fa-file-lines" aria-hidden="true"></i></span>
                      <div class="teacher-dashboard__resource-copy">
                        <a href="{{ asset('storage/'.$resource->path) }}" target="_blank" rel="noopener noreferrer">{{ $resource->name }}</a>
                        <span>{{ $resource->course->name }} &middot; {{ $resource->week ?: 'Sin semana indicada' }}</span>
                        <details class="teacher-dashboard__resource-details" open>
                          <summary>Detalles del archivo</summary>
                          <dl class="teacher-dashboard__resource-metadata">
                            <div><dt>Carpeta</dt><dd>Recursos Pedagógicos</dd></div>
                            <div><dt>Subcarpeta / grado</dt><dd>{{ \App\Support\StoredFileSections::subfolderFromPath('Recursos_Pedagogicos', $resource->path) ?? 'Sin subcarpeta' }}</dd></div>
                            <div><dt>Curso</dt><dd>{{ $resource->course?->name ?? 'Curso eliminado' }}</dd></div>
                            <div><dt>Semana o unidad</dt><dd>{{ $resource->week ?: 'No indicada' }}</dd></div>
                            <div><dt>Subido por</dt><dd>{{ $user->name }}</dd></div>
                            <div><dt>Tipo</dt><dd>{{ $fileTypeOptions[$resource->storedFile?->file_type] ?? 'No especificado' }}</dd></div>
                            <div><dt>Formato</dt><dd>{{ $resource->mime ?: 'No especificado' }}</dd></div>
                            <div><dt>Tamaño</dt><dd>{{ $resource->size >= 1048576 ? number_format($resource->size / 1048576, 2).' MB' : number_format($resource->size / 1024, 1).' KB' }}</dd></div>
                            <div><dt>Fecha de publicación</dt><dd>{{ $resource->storedFile?->published_at?->format('d/m/Y H:i') ?? 'No publicada' }}</dd></div>
                            <div><dt>Fecha de carga</dt><dd>{{ $resource->created_at->format('d/m/Y H:i') }}</dd></div>
                            <div><dt>Fecha de inicio</dt><dd>{{ $resource->storedFile?->available_from?->format('d/m/Y') ?? 'Sin fecha de inicio' }}</dd></div>
                            <div><dt>Fecha de fin</dt><dd>{{ $resource->storedFile?->available_until?->format('d/m/Y') ?? 'Sin fecha de fin' }}</dd></div>
                            <div><dt>Descripción</dt><dd>{{ $resource->storedFile?->description ?: 'No se proporcionó descripción' }}</dd></div>
                            <div><dt>Ubicación</dt><dd>storage/app/public/{{ $resource->path }}</dd></div>
                          </dl>
                        </details>
                      </div>
                      <form method="POST" action="{{ route('teacher.resources.destroy', $resource->id) }}" class="confirm-delete teacher-dashboard__resource-action">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" type="submit" aria-label="Eliminar {{ $resource->name }}">Eliminar</button>
                      </form>
                    </article>
                  @endforeach
                </div>
                {{ $resources->links() }}
              @endif

              @if($otherFiles->isNotEmpty())
                <h3 class="teacher-dashboard__section-kicker">Otros archivos compartidos</h3>
                <div class="teacher-dashboard__resource-list">
                  @foreach($otherFiles as $file)
                    <article class="teacher-dashboard__resource">
                      <span class="teacher-dashboard__resource-icon"><i class="fas fa-file-lines" aria-hidden="true"></i></span>
                      <div class="teacher-dashboard__resource-copy">
                        <a href="{{ asset('storage/'.$file->path) }}" target="_blank" rel="noopener noreferrer">{{ $file->name }}</a>
                        <span>
                          {{ $sectionOptions[$file->section] ?? $file->section }}
                        </span>
                        <details class="teacher-dashboard__resource-details" open>
                          <summary>Detalles del archivo</summary>
                          <dl class="teacher-dashboard__resource-metadata">
                            <div><dt>Carpeta</dt><dd>{{ $sectionOptions[$file->section] ?? $file->section }}</dd></div>
                            <div><dt>Subcarpeta / grado</dt><dd>{{ \App\Support\StoredFileSections::subfolderFromPath($file->section, $file->path) ?? 'Sin subcarpeta' }}</dd></div>
                            <div><dt>Curso</dt><dd>{{ $file->course?->name ?? 'No aplica' }}</dd></div>
                            <div><dt>Subido por</dt><dd>{{ $user->name }}</dd></div>
                            <div><dt>Tipo</dt><dd>{{ $fileTypeOptions[$file->file_type] ?? 'No especificado' }}</dd></div>
                            <div><dt>Formato</dt><dd>{{ $file->mime ?: 'No especificado' }}</dd></div>
                            <div><dt>Tamaño</dt><dd>{{ $file->size >= 1048576 ? number_format($file->size / 1048576, 2).' MB' : number_format($file->size / 1024, 1).' KB' }}</dd></div>
                            <div><dt>Visibilidad</dt><dd>{{ $file->visibility === 'published' ? 'Publicado' : 'Privado' }}</dd></div>
                            <div><dt>Fecha de publicación</dt><dd>{{ $file->published_at?->format('d/m/Y H:i') ?? 'No publicada' }}</dd></div>
                            <div><dt>Fecha de carga</dt><dd>{{ $file->created_at->format('d/m/Y H:i') }}</dd></div>
                            <div><dt>Disponible desde</dt><dd>{{ $file->available_from?->format('d/m/Y') ?? 'Sin fecha de inicio' }}</dd></div>
                            <div><dt>Disponible hasta</dt><dd>{{ $file->available_until?->format('d/m/Y') ?? 'Sin fecha de fin' }}</dd></div>
                            <div><dt>Descripción</dt><dd>{{ $file->description ?: 'No se proporcionó descripción' }}</dd></div>
                            <div><dt>Ubicación</dt><dd>storage/app/public/{{ $file->path }}</dd></div>
                          </dl>
                        </details>
                      </div>
                      <form method="POST" action="{{ route('teacher.resources.files.destroy', $file->id) }}" class="confirm-delete teacher-dashboard__resource-action">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" type="submit" aria-label="Eliminar {{ $file->name }}">Eliminar</button>
                      </form>
                    </article>
                  @endforeach
                </div>
                {{ $otherFiles->links() }}
              @endif
            @else
              <div class="teacher-dashboard__empty">
                <i class="fas fa-folder-open" aria-hidden="true"></i>
                <strong>Tu biblioteca está lista para empezar</strong>
                <p>Cuando subas una guía o actividad, aparecerá aquí.</p>
              </div>
            @endif
            </section>
          @else
            <section class="teacher-dashboard__panel teacher-dashboard__page-panel teacher-dashboard__page-panel--narrow" aria-labelledby="teacherDashboardTitle">
            <form class="teacher-dashboard__upload-form" method="POST" action="{{ route('teacher.resources.upload') }}" enctype="multipart/form-data">
              @csrf
              <div class="teacher-dashboard__field">
                <label for="teacherSection">Carpeta de destino</label>
                <select id="teacherSection" name="section" required>
                  @foreach($sectionOptions as $value => $label)
                    <option
                      value="{{ $value }}"
                      data-course-hierarchy="{{ in_array($value, $courseHierarchySections, true) ? 'true' : 'false' }}"
                      data-requires-week="{{ $value === 'Recursos_Pedagogicos' ? 'true' : 'false' }}"
                      @selected(old('section', 'Recursos_Pedagogicos') === $value)
                    >{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div
                class="teacher-dashboard__field"
                data-upload-course-field
                @if(!in_array(old('section', 'Recursos_Pedagogicos'), $courseHierarchySections, true)) hidden @endif
              >
                <label for="teacherCourse">Curso</label>
                <select
                  id="teacherCourse"
                  name="course_id"
                  @if(in_array(old('section', 'Recursos_Pedagogicos'), $courseHierarchySections, true)) required @endif
                  @disabled($courses->isEmpty() || !in_array(old('section', 'Recursos_Pedagogicos'), $courseHierarchySections, true))
                >
                  <option value="">Selecciona un curso</option>
                  @foreach($courses as $course)
                    <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->name }}</option>
                  @endforeach
                </select>
              </div>
              <div
                class="teacher-dashboard__field"
                data-upload-subfolder-field
                @if(!in_array(old('section', 'Recursos_Pedagogicos'), $courseHierarchySections, true)) hidden @endif
              >
                <label for="teacherSubfolder">Grado / subcarpeta</label>
                <select
                  id="teacherSubfolder"
                  name="subfolder"
                  @if(in_array(old('section', 'Recursos_Pedagogicos'), $courseHierarchySections, true)) required @endif
                  @if(!in_array(old('section', 'Recursos_Pedagogicos'), $courseHierarchySections, true)) disabled @endif
                >
                  <option value="" disabled {{ old('subfolder') ? '' : 'selected' }}>Selecciona un grado</option>
                  @foreach($subfolderOptions as $section => $options)
                    @foreach($options as $value => $label)
                      <option
                        value="{{ $value }}"
                        data-section="{{ $section }}"
                        @selected(old('section', 'Recursos_Pedagogicos') === $section && old('subfolder') === $value)
                      >{{ $label }}</option>
                    @endforeach
                  @endforeach
                </select>
              </div>
              <div class="teacher-dashboard__field">
                <label for="teacherFileType">Tipo de archivo</label>
                <select id="teacherFileType" name="file_type" required>
                  <option value="auto" @selected(old('file_type', 'auto') === 'auto')>Detectar automáticamente</option>
                  @foreach($fileTypeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('file_type') === $value)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="teacher-dashboard__field">
                <label for="teacherFileDescription">Descripción</label>
                <textarea
                  id="teacherFileDescription"
                  name="description"
                  rows="3"
                  maxlength="2000"
                  placeholder="Agrega el propósito, contenido o indicaciones del archivo."
                >{{ old('description') }}</textarea>
              </div>
              <div class="teacher-dashboard__field">
                <label for="teacherAvailableFrom">Fecha de inicio (opcional)</label>
                <input
                  id="teacherAvailableFrom"
                  name="available_from"
                  type="date"
                  value="{{ old('available_from') }}"
                />
              </div>
              <div class="teacher-dashboard__field">
                <label for="teacherAvailableUntil">Fecha de fin (opcional)</label>
                <input
                  id="teacherAvailableUntil"
                  name="available_until"
                  type="date"
                  value="{{ old('available_until') }}"
                />
              </div>
              <p class="teacher-dashboard__form-hint">El archivo estará disponible en la web pública dentro de este periodo. Si dejas las fechas vacías, no tendrá límite de disponibilidad.</p>
              <div
                class="teacher-dashboard__field"
                data-upload-week-field
                @if(old('section', 'Recursos_Pedagogicos') !== 'Recursos_Pedagogicos') hidden @endif
              >
                <label for="teacherWeek">Semana o unidad</label>
                <input
                  id="teacherWeek"
                  name="week"
                  type="text"
                  value="{{ old('week') }}"
                  placeholder="Ej. Semana 1"
                  maxlength="50"
                  @if(old('section', 'Recursos_Pedagogicos') === 'Recursos_Pedagogicos') required @endif
                  @if(old('section', 'Recursos_Pedagogicos') !== 'Recursos_Pedagogicos') disabled @endif
                />
              </div>
              <div class="teacher-dashboard__field">
                <label for="teacherFile">Archivo</label>
                <input id="teacherFile" name="file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.mp4,.mp3,.zip" required />
              </div>
              <p class="teacher-dashboard__form-hint">PDF, documentos, presentaciones, imágenes, video, audio o ZIP. Máximo 50 MB.</p>
              <button class="teacher-dashboard__submit" type="submit">
                <i class="fas fa-cloud-arrow-up" aria-hidden="true"></i> Subir recurso
              </button>
            </form>
            </section>
          @endif
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sectionSelect = document.getElementById('teacherSection');
      if (!sectionSelect) return;

      const courseField = document.querySelector('[data-upload-course-field]');
      const courseSelect = document.getElementById('teacherCourse');
      const subfolderField = document.querySelector('[data-upload-subfolder-field]');
      const subfolderSelect = document.getElementById('teacherSubfolder');
      const weekField = document.querySelector('[data-upload-week-field]');
      const weekInput = document.getElementById('teacherWeek');

      const updateUploadFields = () => {
        const selectedOption = sectionSelect.selectedOptions[0];
        const hasCourseHierarchy = selectedOption.dataset.courseHierarchy === 'true';
        const requiresWeek = selectedOption.dataset.requiresWeek === 'true';

        courseField.hidden = !hasCourseHierarchy;
        courseSelect.disabled = !hasCourseHierarchy || courseSelect.options.length <= 1;
        courseSelect.required = hasCourseHierarchy;

        subfolderField.hidden = !hasCourseHierarchy;
        subfolderSelect.disabled = !hasCourseHierarchy;
        subfolderSelect.required = hasCourseHierarchy;
        [...subfolderSelect.options].forEach(option => {
          if (option.dataset.section) {
            option.hidden = option.dataset.section !== selectedOption.value;
          }
        });
        if (!hasCourseHierarchy || subfolderSelect.selectedOptions[0]?.dataset.section !== selectedOption.value) {
          subfolderSelect.value = '';
        }

        weekField.hidden = !requiresWeek;
        weekInput.disabled = !requiresWeek;
        weekInput.required = requiresWeek;
      };

      sectionSelect.addEventListener('change', updateUploadFields);
      updateUploadFields();
    });
  </script>
@endpush
