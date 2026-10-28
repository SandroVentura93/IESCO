@extends('layouts.admin')

@php
  $adminSection = 'assignments';
@endphp

@push('styles')
  <style>
    .assignment-page {
      --assignment-ink: #183b29;
      --assignment-muted: #647568;
      --assignment-green: #17683e;
      --assignment-border: #e3eae4;
      display: grid;
      grid-template-columns: minmax(0, 1.55fr) minmax(260px, .85fr);
      gap: 1.1rem;
      align-items: start;
    }

    .assignment-card {
      min-width: 0;
      padding: clamp(1.15rem, 3vw, 1.75rem) !important;
      border: 1px solid var(--assignment-border) !important;
      border-radius: 18px !important;
      background: linear-gradient(155deg, #fff, #fbfdfb) !important;
      box-shadow: 0 14px 36px rgba(20, 52, 34, .06) !important;
      position: relative;
      overflow: hidden;
    }

    .assignment-card::before {
      position: absolute;
      inset: 0 0 auto;
      height: 3px;
      background: linear-gradient(90deg, #17683e, #a5c6a9 72%, transparent);
      content: "";
    }

    .assignment-heading {
      margin-bottom: 1.35rem;
    }

    .assignment-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      color: #54745d;
      font-size: .7rem;
      font-weight: 800;
      letter-spacing: .09em;
      text-transform: uppercase;
    }

    .assignment-heading h1 {
      margin: .45rem 0 .3rem;
      color: var(--assignment-ink);
      font-size: clamp(1.45rem, 3vw, 1.9rem);
      line-height: 1.2;
    }

    .assignment-heading p,
    .assignment-help {
      margin: 0;
      color: var(--assignment-muted);
      font-size: .82rem;
      line-height: 1.6;
    }

    .assignment-alert {
      margin-bottom: 1rem;
      padding: .8rem 1rem;
      border: 1px solid #cce6d3;
      border-radius: 11px;
      background: #f1faf3;
      color: #245a37;
      font-size: .82rem;
    }

    .assignment-alert--error {
      border-color: #f0d0cc;
      background: #fff5f3;
      color: #923d32;
    }

    .assignment-alert ul {
      margin: .4rem 0 0;
      padding-left: 1.15rem;
    }

    .assignment-fields {
      display: grid;
      gap: 1.05rem;
    }

    .assignment-field {
      display: grid;
      gap: .42rem;
    }

    .assignment-field label {
      color: #304a38;
      font-size: .79rem;
      font-weight: 750;
    }

    .assignment-picker {
      position: relative;
      z-index: 2;
    }

    .assignment-picker.is-open {
      z-index: 5;
    }

    .assignment-picker__icon {
      position: absolute;
      top: 50%;
      left: .9rem;
      z-index: 1;
      color: #63816a;
      font-size: .88rem;
      pointer-events: none;
      transform: translateY(-50%);
      transition: color .18s ease;
    }

    .assignment-picker__toggle {
      position: absolute;
      top: 50%;
      right: 1rem;
      display: grid;
      width: 30px;
      height: 30px;
      place-items: center;
      border: 0;
      border-radius: 8px;
      background: transparent;
      color: #748579;
      cursor: pointer;
      transform: translateY(-50%);
      transition: background .18s ease, color .18s ease;
    }

    .assignment-picker:focus-within .assignment-picker__icon {
      color: var(--assignment-green);
    }

    .assignment-picker__toggle:hover,
    .assignment-picker.is-open .assignment-picker__toggle {
      background: #edf5ee;
      color: var(--assignment-green);
    }

    .assignment-picker__toggle i {
      transition: transform .18s ease;
    }

    .assignment-picker.is-open .assignment-picker__toggle i {
      transform: rotate(180deg);
    }

    .assignment-picker__menu {
      position: absolute;
      top: calc(100% + .4rem);
      right: 0;
      left: 0;
      z-index: 10;
      overflow: auto;
      max-height: 250px;
      padding: .35rem;
      border: 1px solid #dce6dd;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 14px 32px rgba(26, 57, 36, .14);
    }

    .assignment-picker__menu[hidden] {
      display: none;
    }

    .assignment-picker__option {
      display: flex;
      width: 100%;
      align-items: center;
      gap: .7rem;
      padding: .65rem .7rem;
      border: 0;
      border-radius: 8px;
      background: transparent;
      color: #263c2d !important;
      cursor: pointer;
      text-align: left;
      transition: background .15s ease, color .15s ease;
    }

    .assignment-picker__option[hidden],
    .assignment-picker__empty[hidden] {
      display: none;
    }

    .assignment-picker__option:hover,
    .assignment-picker__option:focus-visible,
    .assignment-picker__option.is-active {
      outline: 0;
      background: #f0f6f1;
      color: #155d36 !important;
    }

    .assignment-picker__option-icon {
      display: grid;
      width: 32px;
      height: 32px;
      flex: 0 0 32px;
      place-items: center;
      border-radius: 9px;
      background: #edf4ee;
      color: #477353;
      font-size: .75rem;
    }

    .assignment-picker__option-copy {
      display: grid;
      min-width: 0;
      gap: .12rem;
    }

    .assignment-picker__option-copy strong {
      overflow: hidden;
      color: #263c2d !important;
      font-size: .79rem;
      font-weight: 700;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .assignment-picker__option-copy small {
      overflow: hidden;
      color: #718075 !important;
      font-size: .69rem;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .assignment-picker__empty {
      padding: .8rem;
      color: #718075;
      font-size: .77rem;
      text-align: center;
    }

    .assignment-field input:not([type="hidden"]),
    .assignment-field select {
      width: 100%;
      min-height: 46px;
      padding: .65rem .8rem;
      border: 1px solid #dce6dd;
      border-radius: 11px;
      background: linear-gradient(180deg, #fff, #fcfdfc);
      color: #263c2d;
      font: inherit;
      font-size: .84rem;
      box-shadow: 0 2px 5px rgba(26, 57, 36, .025), inset 0 1px 0 rgba(255, 255, 255, .9);
      transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .assignment-picker input {
      padding-right: 2.6rem;
      padding-left: 2.55rem;
    }

    .assignment-field input:not([type="hidden"]):focus,
    .assignment-field select:focus {
      outline: 0;
      border-color: var(--assignment-green);
      background: #fff;
      box-shadow: 0 0 0 4px rgba(23, 104, 62, .1), 0 5px 15px rgba(23, 104, 62, .07);
    }

    .assignment-field input:not([type="hidden"])::placeholder {
      color: #89968c;
      opacity: 1;
    }

    .assignment-field [aria-invalid="true"] {
      border-color: #c94d40;
    }

    .assignment-field-error {
      color: #a03f34;
      font-size: .74rem;
    }

    .assignment-actions {
      display: flex;
      justify-content: flex-end;
      gap: .6rem;
      margin-top: 1.25rem;
      padding-top: 1rem;
      border-top: 1px solid #edf1ed;
    }

    .assignment-actions .btn {
      min-height: 42px;
      border-radius: 9px;
      font-size: .79rem;
      font-weight: 750;
    }

    .assignment-actions .btn:disabled {
      cursor: not-allowed;
      opacity: .55;
    }

    .assignment-preview {
      position: sticky;
      top: 1rem;
      overflow: hidden;
      padding: 1.2rem !important;
      border: 1px solid #dce8de !important;
      border-radius: 16px !important;
      background: linear-gradient(145deg, #f7fbf7, #fff) !important;
      box-shadow: 0 12px 30px rgba(20, 52, 34, .05) !important;
    }

    .assignment-preview__title {
      margin: 0 0 .25rem;
      color: var(--assignment-ink);
      font-size: .98rem;
    }

    .assignment-preview__intro {
      margin: 0 0 1rem;
      color: var(--assignment-muted);
      font-size: .75rem;
      line-height: 1.55;
    }

    .assignment-preview__teacher {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .85rem;
      border: 1px solid #e4ece5;
      border-radius: 12px;
      background: rgba(255, 255, 255, .85);
    }

    .assignment-preview__avatar {
      display: grid;
      width: 44px;
      height: 44px;
      flex: 0 0 44px;
      place-items: center;
      border-radius: 13px;
      background: #e9f3eb;
      color: var(--assignment-green);
      font-size: 1rem;
      font-weight: 800;
    }

    .assignment-preview__name {
      overflow-wrap: anywhere;
      color: #263d2d;
      font-size: .83rem;
      font-weight: 750;
    }

    .assignment-preview__email {
      overflow-wrap: anywhere;
      color: var(--assignment-muted);
      font-size: .72rem;
    }

    .assignment-preview__details {
      display: grid;
      gap: .7rem;
      margin: 1rem 0 0;
    }

    .assignment-preview__detail {
      display: grid;
      gap: .18rem;
      padding-bottom: .65rem;
      border-bottom: 1px solid #e8eee9;
    }

    .assignment-preview__detail:last-child {
      padding-bottom: 0;
      border-bottom: 0;
    }

    .assignment-preview__detail dt {
      color: #718075;
      font-size: .68rem;
      font-weight: 750;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .assignment-preview__detail dd {
      margin: 0;
      color: #294332;
      font-size: .81rem;
      font-weight: 650;
      overflow-wrap: anywhere;
    }

    .assignment-empty {
      margin-top: .4rem;
      padding: .75rem .85rem;
      border: 1px solid #f0dfb7;
      border-radius: 10px;
      background: #fffaf0;
      color: #775a20;
      font-size: .77rem;
      line-height: 1.55;
    }

    .assignment-empty a {
      color: #17683e;
      font-weight: 750;
      text-decoration: underline;
      text-underline-offset: 2px;
    }

    .assignment-records {
      margin-top: 1.1rem;
      padding: clamp(1rem, 2.6vw, 1.5rem) !important;
      border: 1px solid #e3eae4 !important;
      border-radius: 18px !important;
      background: #fff !important;
      box-shadow: 0 12px 32px rgba(20, 52, 34, .045) !important;
    }

    .assignment-records__header,
    .assignment-records__tools {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    .assignment-records__header {
      margin-bottom: 1rem;
    }

    .assignment-records__header h2 {
      margin: 0;
      color: var(--assignment-ink);
      font-size: 1.05rem;
    }

    .assignment-records__header p {
      margin: .25rem 0 0;
      color: var(--assignment-muted);
      font-size: .76rem;
    }

    .assignment-records__count {
      flex: 0 0 auto;
      padding: .35rem .65rem;
      border: 1px solid #dce9de;
      border-radius: 999px;
      background: #f3f8f4;
      color: #356044;
      font-size: .72rem;
      font-weight: 750;
    }

    .assignment-records__tools {
      margin-bottom: .8rem;
    }

    .assignment-records__search {
      display: flex;
      width: min(100%, 440px);
      gap: .45rem;
    }

    .assignment-records__search input {
      min-width: 0;
      min-height: 40px;
      flex: 1;
      padding: .55rem .7rem;
      border: 1px solid #dce6dd;
      border-radius: 9px;
      color: #263c2d;
      font: inherit;
      font-size: .78rem;
    }

    .assignment-records__search .btn {
      min-height: 40px;
      border-radius: 9px;
      font-size: .75rem;
    }

    .assignment-records__table-wrap {
      overflow-x: auto;
      border: 1px solid #e8eee9;
      border-radius: 12px;
    }

    .assignment-records__table {
      width: 100%;
      border-collapse: collapse;
      color: #33483a;
      font-size: .77rem;
    }

    .assignment-records__table th {
      padding: .72rem .8rem;
      background: #f5f8f5;
      color: #607265;
      font-size: .67rem;
      font-weight: 800;
      letter-spacing: .045em;
      text-align: left;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .assignment-records__table td {
      padding: .72rem .8rem;
      border-top: 1px solid #edf1ed;
      vertical-align: middle;
    }

    .assignment-records__table tbody tr:hover {
      background: #fbfdfb;
    }

    .assignment-records__teacher,
    .assignment-records__course {
      display: grid;
      gap: .15rem;
    }

    .assignment-records__teacher strong,
    .assignment-records__course strong {
      color: #273e2e;
      font-size: .78rem;
    }

    .assignment-records__teacher small {
      color: #748176;
      font-size: .68rem;
    }

    .assignment-records__badge {
      display: inline-flex;
      padding: .27rem .55rem;
      border: 1px solid #e0eae1;
      border-radius: 999px;
      background: #f4f8f4;
      color: #365b40;
      font-size: .7rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .assignment-records__actions {
      display: flex;
      justify-content: flex-end;
      gap: .4rem;
    }

    .assignment-records__action {
      display: inline-grid;
      min-width: 34px;
      min-height: 34px;
      place-items: center;
      border: 1px solid #dfe8e0;
      border-radius: 8px;
      background: #fff;
      color: #365b40 !important;
      text-decoration: none;
    }

    .assignment-records__action:hover {
      border-color: #b9d2bd;
      background: #f0f6f1;
    }

    .assignment-records__action--delete {
      color: #a5443b !important;
    }

    .assignment-records__empty {
      padding: 1.4rem 1rem;
      color: #718075;
      font-size: .8rem;
      text-align: center;
    }

    .assignment-records__pagination {
      margin-top: .9rem;
    }

    @media (max-width: 820px) {
      .assignment-page {
        grid-template-columns: minmax(0, 1fr);
      }

      .assignment-preview {
        position: static;
      }
    }

    @media (max-width: 520px) {
      .assignment-actions {
        flex-direction: column-reverse;
      }

      .assignment-actions .btn {
        justify-content: center;
        width: 100%;
      }

      .assignment-records__header,
      .assignment-records__tools {
        align-items: stretch;
        flex-direction: column;
      }

      .assignment-records__search {
        width: 100%;
      }

      .assignment-records__table {
        min-width: 720px;
      }
    }
  </style>
@endpush

@section('admin_content')
  @php
    $hasTeachers = $teachers->isNotEmpty();
    $hasCourses = $courses->isNotEmpty();
    $canAssign = $hasTeachers && $hasCourses;
    $selectedTeacherId = old('teacher_id', $assignment?->user_id);
    $selectedCourseId = old('course_id', $assignment?->course_id);
  @endphp

  <div class="assignment-page">
    <section class="admin-card assignment-card" aria-labelledby="assignmentTitle">
      <header class="assignment-heading">
        <span class="assignment-eyebrow"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i> Organización académica</span>
        <h1 id="assignmentTitle">{{ $assignment ? 'Editar asignación' : 'Asignar curso a docente' }}</h1>
        <p>{{ $assignment ? 'Actualiza el docente, curso, grado o sección de esta asignación.' : 'Vincula un docente con una asignatura, su grado y, si corresponde, su sección.' }}</p>
      </header>

      @if(session('success'))
        <div class="assignment-alert" role="status">{{ session('success') }}</div>
      @endif

      @if($errors->any())
        <div class="assignment-alert assignment-alert--error" role="alert" aria-labelledby="assignmentErrorsTitle">
          <strong id="assignmentErrorsTitle">No se pudo guardar la asignación.</strong>
          <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @unless($canAssign)
        <div class="assignment-empty" role="status">
          @if(!$hasTeachers && !$hasCourses)
            Aún no hay docentes ni cursos para asignar. <a href="{{ route('admin.users.create') }}">Registrar docente</a> y <a href="{{ route('admin.courses.create') }}">crear curso</a>.
          @elseif(!$hasTeachers)
            Aún no hay docentes disponibles. <a href="{{ route('admin.users.create') }}">Registrar un docente</a> para continuar.
          @else
            Aún no hay cursos disponibles. <a href="{{ route('admin.courses.create') }}">Crear un curso</a> para continuar.
          @endif
        </div>
      @endunless

      <form method="POST" action="{{ $assignment ? route('admin.assignments.update', $assignment->id) : route('admin.assignments.store') }}" class="assignment-form" autocomplete="off">
        @csrf
        @if($assignment)
          @method('PUT')
        @endif

        <div class="assignment-fields">
          <div class="assignment-field">
            <label for="teacherPicker">Docente</label>
            <div class="assignment-picker">
              <i class="fas fa-user-tie assignment-picker__icon" aria-hidden="true"></i>
              <input
                id="teacherPicker"
                type="text"
                value="{{ $teachers->firstWhere('id', $selectedTeacherId)?->name }}{{ $teachers->firstWhere('id', $selectedTeacherId) ? ' ('.$teachers->firstWhere('id', $selectedTeacherId)->email.')' : '' }}"
                placeholder="Busca o selecciona un docente"
                autocomplete="off"
                required
                role="combobox"
                aria-autocomplete="list"
                aria-controls="teacherOptions"
                aria-expanded="false"
                @disabled(!$hasTeachers)
                @if($errors->has('teacher_id')) aria-invalid="true" aria-describedby="teacherError" @endif
              />
              <button class="assignment-picker__toggle" type="button" aria-label="Mostrar docentes" aria-controls="teacherOptions" aria-expanded="false" @disabled(!$hasTeachers)>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
              </button>
              <div class="assignment-picker__menu" id="teacherOptions" role="listbox" aria-label="Docentes disponibles" hidden>
                @foreach($teachers as $teacher)
                  <button
                    class="assignment-picker__option"
                    type="button"
                    role="option"
                    data-id="{{ $teacher->id }}"
                    data-label="{{ $teacher->name }} ({{ $teacher->email }})"
                    data-name="{{ $teacher->name }}"
                    data-email="{{ $teacher->email }}"
                    aria-selected="{{ (string) old('teacher_id') === (string) $teacher->id ? 'true' : 'false' }}"
                  >
                    <span class="assignment-picker__option-icon"><i class="fas fa-user" aria-hidden="true"></i></span>
                    <span class="assignment-picker__option-copy">
                      <strong>{{ $teacher->name }}</strong>
                      <small>{{ $teacher->email }}</small>
                    </span>
                  </button>
                @endforeach
                <div class="assignment-picker__empty" hidden>No hay coincidencias.</div>
              </div>
            </div>
            <input
              name="teacher_id"
              id="teacher_id"
              type="hidden"
              value="{{ $selectedTeacherId }}"
              @disabled(!$hasTeachers)
            />
            <span id="teacherHelp" class="assignment-help">Escribe para filtrar o elige una cuenta con rol docente.</span>
            @error('teacher_id')
              <span id="teacherError" class="assignment-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="assignment-field">
            <label for="coursePicker">Curso</label>
            <div class="assignment-picker">
              <i class="fas fa-book-open assignment-picker__icon" aria-hidden="true"></i>
              <input
                id="coursePicker"
                type="text"
                value="{{ $courses->firstWhere('id', $selectedCourseId)?->name }}"
                placeholder="Busca o selecciona un curso"
                autocomplete="off"
                required
                role="combobox"
                aria-autocomplete="list"
                aria-controls="courseOptions"
                aria-expanded="false"
                @disabled(!$hasCourses)
                @if($errors->has('course_id')) aria-invalid="true" aria-describedby="courseError" @endif
              />
              <button class="assignment-picker__toggle" type="button" aria-label="Mostrar cursos" aria-controls="courseOptions" aria-expanded="false" @disabled(!$hasCourses)>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
              </button>
              <div class="assignment-picker__menu" id="courseOptions" role="listbox" aria-label="Cursos disponibles" hidden>
                @foreach($courses as $course)
                  <button
                    class="assignment-picker__option"
                    type="button"
                    role="option"
                    data-id="{{ $course->id }}"
                    data-label="{{ $course->name }}"
                    aria-selected="{{ (string) old('course_id') === (string) $course->id ? 'true' : 'false' }}"
                  >
                    <span class="assignment-picker__option-icon"><i class="fas fa-book" aria-hidden="true"></i></span>
                    <span class="assignment-picker__option-copy"><strong>{{ $course->name }}</strong></span>
                  </button>
                @endforeach
                <div class="assignment-picker__empty" hidden>No hay coincidencias.</div>
              </div>
            </div>
            <input
              name="course_id"
              id="course_id"
              type="hidden"
              value="{{ $selectedCourseId }}"
              @disabled(!$hasCourses)
            />
            <span id="courseHelp" class="assignment-help">Escribe para filtrar o elige la asignatura que impartirá el docente.</span>
            @error('course_id')
              <span id="courseError" class="assignment-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="assignment-field">
            <label for="grade">Grado</label>
            <select
              name="grade"
              id="grade"
              required
              aria-describedby="gradeHelp @if($errors->has('grade')) gradeError @endif"
              @class(['form-control', 'is-invalid' => $errors->has('grade')])
              @if($errors->has('grade')) aria-invalid="true" @endif
            >
              <option value="">Selecciona un grado</option>
              @foreach(\App\Models\CourseAssignment::GRADES as $grade)
                <option value="{{ $grade }}" @selected(old('grade', $assignment?->grade) === $grade)>{{ $grade }}</option>
              @endforeach
            </select>
            <span id="gradeHelp" class="assignment-help">Selecciona uno de los grados registrados en el sistema.</span>
            @error('grade')
              <span id="gradeError" class="assignment-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="assignment-field">
            <label for="section">Sección <span class="assignment-help">(opcional)</span></label>
            <input
              type="text"
              name="section"
              id="section"
              value="{{ old('section', $assignment?->section) }}"
              maxlength="60"
              placeholder="Ej. A, B o turno mañana"
              aria-describedby="sectionHelp @if($errors->has('section')) sectionError @endif"
              @if($errors->has('section')) aria-invalid="true" @endif
            />
            <span id="sectionHelp" class="assignment-help">Puedes dejarlo vacío si el curso no está dividido en secciones.</span>
            @error('section')
              <span id="sectionError" class="assignment-field-error">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="assignment-actions">
          @if($assignment)
            <a href="{{ route('admin.assignments.create') }}" class="btn btn-outline-dark">Cancelar</a>
          @else
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-dark">Ver cursos</a>
          @endif
          <button class="btn btn-primary" type="submit" @disabled(!$canAssign)>
            <i class="fas {{ $assignment ? 'fa-save' : 'fa-link' }}" aria-hidden="true"></i> {{ $assignment ? 'Guardar cambios' : 'Guardar asignación' }}
          </button>
        </div>
      </form>
    </section>

    <aside class="admin-card assignment-preview" aria-labelledby="assignmentPreviewTitle">
      <h2 class="assignment-preview__title" id="assignmentPreviewTitle">Vista previa</h2>
      <p class="assignment-preview__intro">Confirma los datos antes de guardar la nueva relación.</p>

      <div class="assignment-preview__teacher">
        <div class="assignment-preview__avatar" id="summary-teacher-avatar" aria-hidden="true">D</div>
        <div>
          <div class="assignment-preview__name" id="summary-teacher-name">Sin docente seleccionado</div>
          <div class="assignment-preview__email" id="summary-teacher-email">—</div>
        </div>
      </div>

      <dl class="assignment-preview__details">
        <div class="assignment-preview__detail">
          <dt>Curso</dt>
          <dd id="summary-course-name">Sin curso seleccionado</dd>
        </div>
        <div class="assignment-preview__detail">
          <dt>Grado</dt>
          <dd id="summary-grade">Sin grado seleccionado</dd>
        </div>
        <div class="assignment-preview__detail">
          <dt>Sección</dt>
          <dd id="summary-section">Sin sección</dd>
        </div>
      </dl>
    </aside>
  </div>

  @unless($assignment)
    <section class="admin-card assignment-records" aria-labelledby="assignmentRecordsTitle">
      <header class="assignment-records__header">
        <div>
          <h2 id="assignmentRecordsTitle">Asignaciones registradas</h2>
          <p>Consulta, edita o elimina las relaciones entre docentes y cursos.</p>
        </div>
        <span class="assignment-records__count">{{ number_format($assignmentCount) }} {{ $assignmentCount === 1 ? 'registro' : 'registros' }}</span>
      </header>

      <div class="assignment-records__tools">
        <form method="GET" action="{{ route('admin.assignments.create') }}" class="assignment-records__search" role="search">
          <input type="search" name="search" value="{{ $search }}" placeholder="Buscar docente, correo, curso, grado o sección" aria-label="Buscar asignaciones" />
          <button type="submit" class="btn btn-primary"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
          @if($search !== '')
            <a href="{{ route('admin.assignments.create') }}" class="btn btn-outline-dark">Limpiar</a>
          @endif
        </form>
      </div>

      <div class="assignment-records__table-wrap">
        <table class="assignment-records__table">
          <thead>
            <tr>
              <th>Docente</th>
              <th>Curso</th>
              <th>Grado</th>
              <th>Sección</th>
              <th>Fecha de asignación</th>
              <th><span class="sr-only">Acciones</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($assignments as $record)
              <tr data-assignment-id="{{ $record->id }}">
                <td>
                  <div class="assignment-records__teacher">
                    <strong>{{ $record->teacher?->name ?? 'Docente no disponible' }}</strong>
                    <small>{{ $record->teacher?->email ?? '—' }}</small>
                  </div>
                </td>
                <td><div class="assignment-records__course"><strong>{{ $record->course?->name ?? 'Curso no disponible' }}</strong></div></td>
                <td><span class="assignment-records__badge">{{ $record->grade ?: 'Sin grado' }}</span></td>
                <td>{{ $record->section ?: '—' }}</td>
                <td>{{ $record->created_at?->format('d/m/Y') ?? '—' }}</td>
                <td>
                  <div class="assignment-records__actions">
                    <a class="assignment-records__action" href="{{ route('admin.assignments.edit', $record->id) }}" aria-label="Editar asignación de {{ $record->teacher?->name }} para {{ $record->course?->name }}" title="Editar">
                      <i class="fas fa-pen" aria-hidden="true"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.assignments.destroy', $record->id) }}" onsubmit="return confirm('¿Eliminar esta asignación? El docente dejará de ver el curso en su panel.')">
                      @csrf
                      @method('DELETE')
                      <button class="assignment-records__action assignment-records__action--delete" type="submit" aria-label="Eliminar asignación de {{ $record->teacher?->name }} para {{ $record->course?->name }}" title="Eliminar">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="assignment-records__empty">
                  {{ $search !== '' ? 'No hay asignaciones que coincidan con la búsqueda.' : 'Todavía no hay asignaciones registradas.' }}
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($assignments->hasPages())
        <div class="admin-pagination assignment-records__pagination">{{ $assignments->links() }}</div>
      @endif
    </section>
  @endunless
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      [
        { picker: 'teacherPicker', options: 'teacherOptions', value: 'teacher_id' },
        { picker: 'coursePicker', options: 'courseOptions', value: 'course_id' }
      ].forEach(function (field) {
        const picker = document.getElementById(field.picker);
        const menu = document.getElementById(field.options);
        const hidden = document.getElementById(field.value);
        const wrapper = picker && picker.closest('.assignment-picker');
        const toggle = wrapper && wrapper.querySelector('.assignment-picker__toggle');
        if (!picker || !menu || !hidden || !wrapper || !toggle) return;

        const options = Array.from(menu.querySelectorAll('.assignment-picker__option'));
        const emptyState = menu.querySelector('.assignment-picker__empty');
        let activeIndex = -1;

        function visibleOptions() {
          return options.filter(function (option) {
            return !option.hidden;
          });
        }

        function setOpen(open) {
          menu.hidden = !open;
          wrapper.classList.toggle('is-open', open);
          picker.setAttribute('aria-expanded', String(open));
          toggle.setAttribute('aria-expanded', String(open));
          activeIndex = -1;
          picker.removeAttribute('aria-activedescendant');
          options.forEach(function (option) {
            option.classList.remove('is-active');
          });
        }

        function filterOptions(query) {
          const normalizedQuery = query.trim().toLocaleLowerCase();
          let count = 0;

          options.forEach(function (option) {
            const searchable = (option.dataset.label + ' ' + (option.dataset.email || '')).toLocaleLowerCase();
            option.hidden = normalizedQuery !== '' && !searchable.includes(normalizedQuery);
            if (!option.hidden) count++;
          });

          if (emptyState) emptyState.hidden = count > 0;
          return count;
        }

        function selectOption(option) {
          picker.value = option.dataset.label;
          hidden.value = option.dataset.id;
          hidden.dataset.email = option.dataset.email || '';
          hidden.dataset.name = option.dataset.name || option.dataset.label;
          options.forEach(function (item) {
            item.setAttribute('aria-selected', String(item === option));
          });
          picker.setCustomValidity('');
          setOpen(false);
          picker.dispatchEvent(new Event('change', { bubbles: true }));
          updateSummary();
        }

        function highlightOption(index) {
          const visible = visibleOptions();
          if (!visible.length) return;
          activeIndex = (index + visible.length) % visible.length;

          visible.forEach(function (option, optionIndex) {
            const active = optionIndex === activeIndex;
            option.classList.toggle('is-active', active);
            if (active) {
              picker.setAttribute('aria-activedescendant', option.id);
              option.scrollIntoView({ block: 'nearest' });
            }
          });
        }

        options.forEach(function (option, index) {
          option.id = field.options + 'Option' + index;
          option.addEventListener('click', function () {
            selectOption(option);
            picker.focus();
          });
        });

        picker.addEventListener('input', function () {
          hidden.value = '';
          hidden.dataset.email = '';
          hidden.dataset.name = '';
          picker.setCustomValidity('Selecciona una opción de la lista.');
          filterOptions(picker.value);
          setOpen(true);
          updateSummary();
        });

        picker.addEventListener('click', function () {
          filterOptions(picker.value && !hidden.value ? picker.value : '');
          setOpen(true);
        });

        picker.addEventListener('keydown', function (event) {
          if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (menu.hidden) {
              filterOptions(picker.value);
              setOpen(true);
            }
            const visible = visibleOptions();
            const nextIndex = activeIndex < 0
              ? (event.key === 'ArrowDown' ? 0 : visible.length - 1)
              : activeIndex + (event.key === 'ArrowDown' ? 1 : -1);
            highlightOption(nextIndex);
          } else if (event.key === 'Enter' && !menu.hidden) {
            const visible = visibleOptions();
            if (visible.length) {
              event.preventDefault();
              selectOption(visible[activeIndex >= 0 ? activeIndex : 0]);
            }
          } else if (event.key === 'Escape') {
            setOpen(false);
          }
        });

        toggle.addEventListener('click', function () {
          const open = menu.hidden;
          if (open) {
            filterOptions('');
            setOpen(true);
            picker.focus();
          } else {
            setOpen(false);
          }
        });

        picker.addEventListener('blur', function () {
          window.setTimeout(function () {
            if (!wrapper.contains(document.activeElement)) setOpen(false);
          }, 0);
        });

        const selected = options.find(function (option) {
          return option.dataset.id === hidden.value;
        });
        if (selected) {
          hidden.dataset.email = selected.dataset.email || '';
          hidden.dataset.name = selected.dataset.name || selected.dataset.label;
          selected.setAttribute('aria-selected', 'true');
          if (!picker.value) picker.value = selected.dataset.label;
        } else {
          picker.setCustomValidity('Selecciona una opción de la lista.');
        }
      });

      const teacherInput = document.getElementById('teacherPicker');
      const courseInput = document.getElementById('coursePicker');
      const sectionInput = document.getElementById('section');
      const form = document.querySelector('.assignment-form');
      const summary = {
        name: document.getElementById('summary-teacher-name'),
        email: document.getElementById('summary-teacher-email'),
        avatar: document.getElementById('summary-teacher-avatar'),
        course: document.getElementById('summary-course-name'),
        grade: document.getElementById('summary-grade'),
        section: document.getElementById('summary-section')
      };

      function updateSummary() {
        const teacherValue = document.getElementById('teacher_id');
        if (teacherValue && teacherValue.value && teacherInput) {
          const name = teacherValue.dataset.name || teacherInput.value.split(' (')[0].trim();
          summary.name.textContent = name;
          summary.email.textContent = teacherValue.dataset.email || 'Correo no disponible';
          summary.avatar.textContent = Array.from(name)[0]?.toLocaleUpperCase() || 'D';
        } else {
          summary.name.textContent = 'Sin docente seleccionado';
          summary.email.textContent = '—';
          summary.avatar.textContent = 'D';
        }

        const courseValue = document.getElementById('course_id');
        summary.course.textContent = courseValue && courseValue.value && courseInput
          ? courseInput.value.trim()
          : 'Sin curso seleccionado';
        const gradeInput = document.getElementById('grade');
        summary.grade.textContent = gradeInput && gradeInput.value.trim()
          ? gradeInput.value.trim()
          : 'Sin grado seleccionado';
        summary.section.textContent = sectionInput && sectionInput.value.trim()
          ? sectionInput.value.trim()
          : 'Sin sección';
      }

      [teacherInput, courseInput].forEach(function (input) {
        if (input) {
          input.addEventListener('input', updateSummary);
          input.addEventListener('change', updateSummary);
        }
      });
      const gradeInput = document.getElementById('grade');
      if (gradeInput) gradeInput.addEventListener('input', updateSummary);
      if (gradeInput) gradeInput.addEventListener('change', updateSummary);
      if (sectionInput) sectionInput.addEventListener('input', updateSummary);
      if (form) {
        form.addEventListener('submit', function (event) {
          const invalidPicker = [teacherInput, courseInput].find(function (input) {
            return input && !document.getElementById(input.id === 'teacherPicker' ? 'teacher_id' : 'course_id').value;
          });
          if (invalidPicker) {
            event.preventDefault();
            invalidPicker.setCustomValidity('Selecciona una opción de la lista.');
            invalidPicker.reportValidity();
            invalidPicker.focus();
          }
        });
      }
      updateSummary();
    });
  </script>
@endpush
