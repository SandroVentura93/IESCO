@php
  $enrollment = $enrollment ?? null;
  $editing = $editing ?? false;
  $students = $students ?? collect();
  $academicYear = $academicYear ?? $enrollment?->academic_year ?? now()->year;
  $selectedStudentId = old('student_id', $selectedStudentId ?? $enrollment?->student_id);
  $selectedStudent = $students->firstWhere('id', (int) $selectedStudentId);
@endphp

@push('styles')
  <style>
    .enrollment-form-card {
      max-width: 980px;
      margin: 0 auto;
      padding: clamp(1.1rem, 2.6vw, 2rem) !important;
      border: 1px solid #e4ebe5 !important;
      border-radius: 20px !important;
      background:
        radial-gradient(ellipse at 100% 0, rgba(225, 239, 226, .55), transparent 31%),
        linear-gradient(150deg, #fff, #fbfdfb) !important;
      box-shadow: 0 18px 44px rgba(20, 52, 34, .07) !important;
    }

    .enrollment-form__header {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 1.2rem;
      padding-bottom: 1.1rem;
      border-bottom: 1px solid #e8eee8;
    }

    .enrollment-form__icon {
      display: grid;
      width: 48px;
      height: 48px;
      flex: 0 0 48px;
      place-items: center;
      border: 1px solid #deebdf;
      border-radius: 15px;
      background: linear-gradient(145deg, #edf6ee, #f7f3e9);
      color: #37704a;
    }

    .enrollment-form__eyebrow {
      color: #62816c;
      font-size: .66rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    .enrollment-form__header h1 {
      margin: .2rem 0 .22rem;
      color: #183b29;
      font-size: clamp(1.5rem, 2.9vw, 1.95rem);
      letter-spacing: -.035em;
    }

    .enrollment-form__header p {
      max-width: 620px;
      margin: 0;
      color: #68776c;
      font-size: .8rem;
      line-height: 1.55;
    }

    .enrollment-form__notice,
    .enrollment-form__alert {
      margin-bottom: .8rem;
      padding: .72rem .85rem;
      border: 1px solid #cce6d3;
      border-radius: 10px;
      background: #f1faf3;
      color: #245a37;
      font-size: .76rem;
    }

    .enrollment-form__alerts {
      display: grid;
      gap: .45rem;
      margin-bottom: .8rem;
    }

    .enrollment-form__alert {
      margin: 0;
      border-color: #f0d0cc;
      background: #fff5f3;
      color: #923d32;
    }

    .enrollment-form__section {
      margin-top: 1.05rem;
      padding: 1.1rem;
      border: 1px solid #e2eae2;
      border-radius: 15px;
      background: rgba(255, 255, 255, .96);
      box-shadow: 0 7px 20px rgba(28, 58, 38, .035);
    }

    .enrollment-form__section-title {
      display: flex;
      align-items: center;
      gap: .62rem;
      margin: 0 0 .9rem;
      color: #203e2b;
      font-size: .86rem;
      font-weight: 800;
    }

    .enrollment-form__section-title i {
      display: grid;
      width: 31px;
      height: 31px;
      place-items: center;
      border: 1px solid #dceadd;
      border-radius: 9px;
      background: #f0f7f1;
      color: #37704a;
      font-size: .72rem;
    }

    .enrollment-form__grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .9rem 1rem;
    }

    .enrollment-form__field {
      min-width: 0;
    }

    .enrollment-form__field--wide {
      grid-column: 1 / -1;
    }

    .enrollment-form__field label {
      display: block;
      margin-bottom: .32rem;
      color: #304736;
      font-size: .72rem;
      font-weight: 750;
    }

    .enrollment-form__required {
      color: #a64338;
    }

    .enrollment-form__field input,
    .enrollment-form__field select,
    .enrollment-form__field textarea {
      display: block;
      width: 100%;
      min-height: 45px;
      padding: .65rem .75rem;
      border: 1px solid #d8e2d9;
      border-radius: 10px;
      background: #fff;
      color: #263c2d;
      font: inherit;
      font-size: .79rem;
      transition: border-color .16s ease, box-shadow .16s ease;
    }

    .enrollment-student-picker {
      position: relative;
      min-width: 0;
      flex: 1;
    }

    .enrollment-student-select-row {
      display: flex;
      align-items: stretch;
      gap: .55rem;
    }

    .enrollment-student-add {
      display: inline-flex;
      min-width: 148px;
      min-height: 52px;
      align-items: center;
      justify-content: center;
      gap: .42rem;
      padding: .55rem .75rem;
      border: 1.5px solid #93b99a;
      border-radius: 10px;
      background: linear-gradient(180deg, #fff, #f4f9f4);
      color: #205c34 !important;
      font: inherit;
      font-size: .73rem;
      font-weight: 800;
      box-shadow: 0 3px 8px rgba(34, 89, 48, .08);
      cursor: pointer;
      transition: border-color .16s ease, background .16s ease, transform .16s ease, box-shadow .16s ease;
    }

    .enrollment-student-add:hover {
      border-color: #2c7542;
      background: #edf6ee;
      box-shadow: 0 6px 14px rgba(34, 89, 48, .13);
      transform: translateY(-1px);
    }

    .enrollment-student-add i {
      display: grid;
      width: 25px;
      height: 25px;
      place-items: center;
      border-radius: 7px;
      background: #e4f0e5;
      color: #236b3c;
      font-size: .72rem;
    }

    .enrollment-student-add:focus-visible {
      outline: 0;
      box-shadow: 0 0 0 3px rgba(23, 104, 62, .14);
    }

    .enrollment-student-modal {
      position: fixed;
      inset: 50% auto auto 50%;
      width: min(560px, calc(100% - 1.5rem));
      max-width: none;
      max-height: min(90dvh, 760px);
      margin: 0;
      padding: 0;
      overflow: auto;
      border: 1px solid #d6e3d7;
      border-radius: 20px;
      background: #fff;
      color: #263c2d;
      box-shadow: 0 30px 100px rgba(18, 45, 27, .34);
      transform: translate(-50%, -50%);
    }

    .enrollment-student-modal::backdrop {
      background: rgba(16, 36, 22, .58);
      backdrop-filter: blur(3px);
    }

    .enrollment-student-modal__content {
      padding: clamp(1rem, 3vw, 1.6rem);
    }

    .enrollment-student-modal__header {
      display: flex;
      align-items: flex-start;
      gap: .8rem;
      padding-bottom: .95rem;
      border-bottom: 1px solid #e8eee8;
    }

    .enrollment-student-modal__icon {
      display: grid;
      width: 42px;
      height: 42px;
      flex: 0 0 42px;
      place-items: center;
      border: 1px solid #dceadd;
      border-radius: 12px;
      background: #eff6f0;
      color: #37704a;
    }

    .enrollment-student-modal__heading {
      min-width: 0;
      flex: 1;
    }

    .enrollment-student-modal__eyebrow {
      color: #62816c;
      font-size: .62rem;
      font-weight: 800;
      letter-spacing: .09em;
      text-transform: uppercase;
    }

    .enrollment-student-modal__heading h2 {
      margin: .2rem 0 .22rem;
      color: #183b29;
      font-size: 1.3rem;
      letter-spacing: -.025em;
    }

    .enrollment-student-modal__heading p {
      margin: 0;
      color: #68776c;
      font-size: .72rem;
      line-height: 1.5;
    }

    .enrollment-student-modal__close {
      display: grid;
      width: 40px;
      height: 40px;
      flex: 0 0 40px;
      place-items: center;
      border: 1px solid #d8e2d9;
      border-radius: 11px;
      background: #f8faf8;
      color: #465b4b !important;
      font-size: .85rem;
      cursor: pointer;
    }

    .enrollment-student-modal__close:hover {
      border-color: #a9c5ac;
      background: #edf5ee;
      color: #294c33;
    }

    .enrollment-student-modal__form {
      display: grid;
      gap: .85rem;
      margin-top: 1rem;
    }

    .enrollment-student-modal__grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .8rem;
    }

    .enrollment-student-modal__field {
      min-width: 0;
    }

    .enrollment-student-modal__field--wide {
      grid-column: 1 / -1;
    }

    .enrollment-student-modal__field label {
      display: block;
      margin-bottom: .32rem;
      color: #304736;
      font-size: .7rem;
      font-weight: 750;
    }

    .enrollment-student-modal__field input,
    .enrollment-student-modal__field select {
      display: block;
      width: 100%;
      min-height: 42px;
      padding: .58rem .68rem;
      border: 1px solid #dfe7df;
      border-radius: 9px;
      background: #fff;
      color: #263c2d;
      font: inherit;
      font-size: .75rem;
    }

    .enrollment-student-modal__field input:focus,
    .enrollment-student-modal__field select:focus {
      outline: 0;
      border-color: #8daf93;
      box-shadow: 0 0 0 3px rgba(23, 104, 62, .12);
    }

    .enrollment-student-modal__hint {
      margin: 0;
      color: #748177;
      font-size: .66rem;
      line-height: 1.5;
    }

    .enrollment-student-modal__message {
      padding: .68rem .75rem;
      border: 1px solid #f0d0cc;
      border-radius: 9px;
      background: #fff5f3;
      color: #923d32;
      font-size: .7rem;
    }

    .enrollment-student-modal__message:empty {
      display: none;
    }

    .enrollment-student-modal__actions {
      display: flex;
      justify-content: flex-end;
      gap: .5rem;
      padding-top: 1rem;
      border-top: 1px solid #e3eae3;
    }

    .enrollment-student-modal__actions button {
      display: inline-flex;
      min-height: 46px;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      padding: .62rem .95rem;
      border: 1px solid #cbd9cd;
      border-radius: 10px;
      background: #fff;
      color: #36513c;
      font: inherit;
      font-size: .76rem;
      font-weight: 750;
      cursor: pointer;
      transition: background .16s ease, border-color .16s ease, transform .16s ease, box-shadow .16s ease;
    }

    .enrollment-student-modal__actions button[type="button"]:hover {
      border-color: #9cb69f;
      background: #f5f8f5;
    }

    .enrollment-student-modal__actions button[type="button"] {
      border-color: #a9bdaa;
      background: #f4f8f4;
      color: #2e5737 !important;
      box-shadow: 0 2px 6px rgba(30, 66, 39, .06);
    }

    .enrollment-student-modal__actions [type="submit"] {
      border: 1px solid #135a34;
      background: linear-gradient(135deg, #217345, #125a33);
      color: #fff !important;
      box-shadow: 0 5px 12px rgba(23, 104, 62, .22);
    }

    .enrollment-student-modal__actions [type="submit"]:hover:not(:disabled) {
      background: linear-gradient(135deg, #2a8050, #17683e);
      box-shadow: 0 7px 16px rgba(23, 104, 62, .28);
      transform: translateY(-1px);
    }

    .enrollment-student-modal__actions [type="submit"]:disabled {
      opacity: .65;
      cursor: wait;
    }

    .enrollment-student-picker__trigger {
      display: flex;
      width: 100%;
      min-height: 52px;
      align-items: center;
      gap: .7rem;
      padding: .55rem .7rem;
      border: 1px solid #dfe7df;
      border-radius: 10px;
      background: #fff;
      color: #263c2d;
      font: inherit;
      text-align: left;
      cursor: pointer;
      transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .enrollment-student-picker__trigger:hover,
    .enrollment-student-picker__trigger[aria-expanded="true"] {
      border-color: #a6c4aa;
      background: #fcfefc;
    }

    .enrollment-student-picker__trigger:focus-visible {
      outline: 0;
      border-color: #8daf93;
      box-shadow: 0 0 0 3px rgba(23, 104, 62, .12);
    }

    .enrollment-student-picker__trigger[aria-invalid="true"] {
      border-color: #cf8179;
    }

    .enrollment-student-picker__avatar {
      display: grid;
      width: 34px;
      height: 34px;
      flex: 0 0 34px;
      place-items: center;
      border: 1px solid #dceadd;
      border-radius: 10px;
      background: #eff6f0;
      color: #37704a;
      font-size: .66rem;
      font-weight: 850;
      letter-spacing: .02em;
    }

    .enrollment-student-picker__copy {
      display: block;
      min-width: 0;
      flex: 1;
    }

    .enrollment-student-picker__copy strong,
    .enrollment-student-picker__copy span {
      display: block;
    }

    .enrollment-student-picker__copy strong {
      display: -webkit-box;
      overflow: hidden;
      color: #2d4433;
      font-size: .75rem;
      font-weight: 750;
      line-height: 1.25;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
    }

    .enrollment-student-picker__copy span {
      margin-top: .1rem;
      color: #78857a;
      font-size: .65rem;
    }

    .enrollment-student-picker__placeholder {
      color: #778379;
      font-size: .74rem;
    }

    .enrollment-student-picker__chevron {
      flex: 0 0 auto;
      color: #708174;
      font-size: .7rem;
      transition: transform .16s ease;
    }

    .enrollment-student-picker__trigger[aria-expanded="true"] .enrollment-student-picker__chevron {
      transform: rotate(180deg);
    }

    .enrollment-student-picker__panel {
      position: absolute;
      z-index: 30;
      top: calc(100% + .42rem);
      right: 0;
      left: 0;
      overflow: hidden;
      border: 1px solid #dfe8df;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 18px 40px rgba(22, 48, 30, .16);
    }

    .enrollment-student-picker__panel[hidden] {
      display: none;
    }

    .enrollment-student-picker__search-wrap {
      position: relative;
      padding: .7rem .7rem .55rem;
      border-bottom: 1px solid #edf1ed;
      background: #fbfdfb;
    }

    .enrollment-student-picker__search-icon {
      position: absolute;
      top: 50%;
      left: 1rem;
      color: #819083;
      font-size: .7rem;
      pointer-events: none;
      transform: translateY(-50%);
    }

    .enrollment-student-picker__search {
      width: 100%;
      min-height: 39px;
      padding: .5rem .65rem .5rem 1.8rem;
      border: 1px solid #dfe7df;
      border-radius: 8px;
      background: #fff;
      color: #263c2d;
      font: inherit;
      font-size: .72rem;
    }

    .enrollment-student-picker__search:focus {
      outline: 0;
      border-color: #8daf93;
      box-shadow: 0 0 0 3px rgba(23, 104, 62, .1);
    }

    .enrollment-student-picker__count {
      display: flex;
      justify-content: space-between;
      gap: .5rem;
      padding: .48rem .75rem .38rem;
      color: #78857a;
      font-size: .62rem;
    }

    .enrollment-student-picker__options {
      display: grid;
      max-height: 252px;
      overflow-y: auto;
      padding: .2rem .38rem .42rem;
      list-style: none;
      scrollbar-width: thin;
    }

    .enrollment-student-picker__option {
      display: flex;
      width: 100%;
      align-items: center;
      gap: .65rem;
      padding: .55rem .5rem;
      border: 0;
      border-radius: 8px;
      background: transparent;
      color: #304736;
      font: inherit;
      text-align: left;
      cursor: pointer;
    }

    .enrollment-student-picker__option:hover,
    .enrollment-student-picker__option:focus-visible,
    .enrollment-student-picker__option[aria-selected="true"] {
      outline: 0;
      background: #f0f7f1;
    }

    .enrollment-student-picker__option .enrollment-student-picker__avatar {
      width: 32px;
      height: 32px;
      flex-basis: 32px;
      border-radius: 9px;
    }

    .enrollment-student-picker__option-text {
      min-width: 0;
      flex: 1;
    }

    .enrollment-student-picker__option-text strong,
    .enrollment-student-picker__option-text span {
      display: block;
    }

    .enrollment-student-picker__option-text strong {
      display: -webkit-box;
      overflow: hidden;
      color: #304736;
      font-size: .7rem;
      font-weight: 750;
      line-height: 1.25;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
    }

    .enrollment-student-picker__option-text span {
      margin-top: .1rem;
      color: #78857a;
      font-size: .62rem;
    }

    .enrollment-student-picker__option-check {
      margin-left: auto;
      color: #287345;
      font-size: .7rem;
      opacity: 0;
    }

    .enrollment-student-picker__option[aria-selected="true"] .enrollment-student-picker__option-check {
      opacity: 1;
    }

    .enrollment-student-picker__empty {
      padding: 1rem .75rem;
      color: #78857a;
      font-size: .7rem;
      text-align: center;
    }

    .enrollment-student-picker__empty[hidden],
    .enrollment-student-picker__error:not(.is-visible) {
      display: none;
    }

    .enrollment-student-picker__error.is-visible {
      display: block;
    }

    .enrollment-form__field textarea {
      min-height: 84px;
      resize: vertical;
    }

    .enrollment-form__field input:focus,
    .enrollment-form__field select:focus,
    .enrollment-form__field textarea:focus {
      outline: 0;
      border-color: #8daf93;
      box-shadow: 0 0 0 3px rgba(23, 104, 62, .12);
    }

    .enrollment-form__field [aria-invalid="true"] {
      border-color: #cf8179;
    }

    .enrollment-form__help,
    .enrollment-form__error {
      display: block;
      margin-top: .28rem;
      font-size: .66rem;
      line-height: 1.45;
    }

    .enrollment-form__help {
      color: #748177;
    }

    .enrollment-form__help a {
      color: #17683e;
      font-weight: 750;
      text-decoration: underline;
      text-underline-offset: 2px;
    }

    .enrollment-form__error {
      color: #a43e34;
      font-weight: 650;
    }

    .enrollment-form__student-summary {
      display: flex;
      align-items: center;
      gap: .65rem;
      min-height: 41px;
      padding: .5rem .65rem;
      border: 1px solid #e3ece4;
      border-radius: 9px;
      background: #f7faf7;
      color: #294333;
      font-size: .76rem;
    }

    .enrollment-form__student-summary i {
      color: #50805c;
    }

    .enrollment-form__student-summary strong,
    .enrollment-form__student-summary span {
      display: block;
    }

    .enrollment-form__student-summary span {
      color: #748177;
      font-size: .66rem;
    }

    .enrollment-form__footer {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1.25rem;
      margin-top: 1.35rem;
      padding: 1.1rem 0 .1rem;
      border-top: 1px solid #dfe8df;
    }

    .enrollment-form__footer-note {
      max-width: 360px;
      color: #748177;
      font-size: .7rem;
      line-height: 1.5;
    }

    .enrollment-form__actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: .6rem;
    }

    .enrollment-form__actions .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      min-height: 48px !important;
      padding: .7rem 1.05rem !important;
      border-radius: 10px !important;
      font-size: .78rem !important;
      font-weight: 800 !important;
      letter-spacing: .01em;
      text-decoration: none;
      transition: background .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .enrollment-form__actions .btn-outline-dark {
      border: 1.5px solid #9fb5a2 !important;
      background: #fff !important;
      color: #365b3f !important;
      box-shadow: 0 2px 6px rgba(30, 66, 39, .05);
    }

    .enrollment-form__actions .btn-outline-dark:hover {
      border-color: #5e8c66 !important;
      background: #f0f7f1 !important;
      color: #214b2e !important;
    }

    .enrollment-form__actions .btn-primary {
      border: 1px solid #135a34 !important;
      background: linear-gradient(135deg, #217345, #125a33) !important;
      color: #fff !important;
      box-shadow: 0 5px 12px rgba(23, 104, 62, .2);
    }

    .enrollment-form__actions .btn-primary:hover {
      background: linear-gradient(135deg, #2a8050, #17683e) !important;
      box-shadow: 0 8px 18px rgba(23, 104, 62, .26);
      transform: translateY(-1px);
    }

    .enrollment-form__actions .btn:focus-visible,
    .enrollment-student-modal__actions button:focus-visible,
    .enrollment-student-modal__close:focus-visible {
      outline: 3px solid rgba(29, 113, 57, .28);
      outline-offset: 2px;
    }

    .enrollment-form__uppercase {
      text-transform: uppercase;
    }

    @media (max-width: 620px) {
      .enrollment-form__grid {
        grid-template-columns: minmax(0, 1fr);
        gap: .72rem;
      }

      .enrollment-form__field--wide {
        grid-column: auto;
      }

      .enrollment-form__footer {
        align-items: stretch;
        flex-direction: column;
        gap: .8rem;
      }

      .enrollment-form__actions {
        width: 100%;
        align-items: stretch;
        flex-direction: column-reverse;
      }

      .enrollment-form__actions .btn {
        width: 100%;
        min-height: 50px !important;
      }

      .enrollment-student-select-row {
        align-items: stretch;
        flex-direction: column;
      }

      .enrollment-student-add {
        width: 100%;
        min-height: 48px;
      }

      .enrollment-student-modal__grid {
        grid-template-columns: minmax(0, 1fr);
        gap: .7rem;
      }

      .enrollment-student-modal__field--wide {
        grid-column: auto;
      }

      .enrollment-student-modal__actions {
        flex-direction: column-reverse;
      }

      .enrollment-student-modal__actions button {
        width: 100%;
        min-height: 49px;
      }
    }
  </style>
@endpush

<section class="admin-card enrollment-form-card" aria-labelledby="enrollmentFormTitle">
  <header class="enrollment-form__header">
    <span class="enrollment-form__icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
    <div>
      <span class="enrollment-form__eyebrow">Proceso de matrícula</span>
      <h1 id="enrollmentFormTitle">{{ $formTitle }}</h1>
      <p>{{ $formDescription }}</p>
    </div>
  </header>

  @if(session('success'))
    <div class="enrollment-form__notice" role="status">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="enrollment-form__alerts" role="alert">
      @foreach($errors->all() as $error)
        <div class="enrollment-form__alert">{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ $formAction }}">
    @csrf
    @if($editing)
      @method('PUT')
    @endif

    <section class="enrollment-form__section" aria-labelledby="enrollmentStudentSection">
      <h2 class="enrollment-form__section-title" id="enrollmentStudentSection"><i class="fas fa-user-graduate" aria-hidden="true"></i> Datos del alumno</h2>
      <div class="enrollment-form__grid">
        <div class="enrollment-form__field enrollment-form__field--wide">
          <label for="enrollmentStudent">Alumno <span class="enrollment-form__required" aria-hidden="true">*</span></label>
          @if($editing)
            <input type="hidden" name="student_id" value="{{ $enrollment->student_id }}" />
            <div class="enrollment-form__student-summary">
              <i class="fas fa-user-check" aria-hidden="true"></i>
              <div><strong>{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</strong><span>DNI: {{ $enrollment->student->dni }}</span></div>
            </div>
          @else
            <div class="enrollment-student-select-row">
              <div class="enrollment-student-picker" data-enrollment-student-picker>
                <input type="hidden" name="student_id" value="{{ $selectedStudentId }}" data-enrollment-student-value />
                <button
                  id="enrollmentStudent"
                  class="enrollment-student-picker__trigger"
                  type="button"
                  role="combobox"
                  aria-haspopup="listbox"
                  aria-expanded="false"
                  aria-controls="enrollmentStudentOptions"
                  aria-required="true"
                  aria-label="Alumno"
                  @error('student_id') aria-invalid="true" aria-describedby="enrollmentStudentError" @enderror
                  data-enrollment-student-trigger
                >
                  @if($selectedStudent)
                    <span class="enrollment-student-picker__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($selectedStudent->first_name, 0, 1, 'UTF-8').mb_substr($selectedStudent->last_name, 0, 1, 'UTF-8'), 'UTF-8') }}</span>
                    <span class="enrollment-student-picker__copy">
                      <strong>{{ $selectedStudent->last_name }}, {{ $selectedStudent->first_name }}</strong>
                      <span>DNI {{ $selectedStudent->dni }}</span>
                    </span>
                  @else
                    <span class="enrollment-student-picker__avatar" aria-hidden="true"><i class="fas fa-user-graduate"></i></span>
                    <span class="enrollment-student-picker__copy">
                      <strong>Selecciona un alumno</strong>
                      <span>Busca por nombre, apellido o DNI</span>
                    </span>
                  @endif
                  <i class="fas fa-chevron-down enrollment-student-picker__chevron" aria-hidden="true"></i>
                </button>

                <div id="enrollmentStudentPanel" class="enrollment-student-picker__panel" data-enrollment-student-panel hidden>
                  <div class="enrollment-student-picker__search-wrap">
                    <i class="fas fa-search enrollment-student-picker__search-icon" aria-hidden="true"></i>
                    <input
                      class="enrollment-student-picker__search"
                      type="search"
                      placeholder="Buscar alumno por nombre o DNI..."
                      aria-label="Buscar alumno por nombre, apellido o DNI"
                      aria-controls="enrollmentStudentOptions"
                      autocomplete="off"
                      data-enrollment-student-search
                    />
                  </div>
                  <div class="enrollment-student-picker__count">
                    <span data-enrollment-student-count>{{ $students->count() }} {{ $students->count() === 1 ? 'alumno disponible' : 'alumnos disponibles' }}</span>
                    <span>Escribe para filtrar la lista</span>
                  </div>
                  <ul id="enrollmentStudentOptions" class="enrollment-student-picker__options" role="listbox" aria-label="Alumnos disponibles">
                    @foreach($students as $student)
                      @php
                        $studentName = $student->last_name.', '.$student->first_name;
                        $studentInitials = mb_strtoupper(mb_substr($student->first_name, 0, 1, 'UTF-8').mb_substr($student->last_name, 0, 1, 'UTF-8'), 'UTF-8');
                      @endphp
                      <li data-enrollment-student-item>
                        <button
                          type="button"
                          class="enrollment-student-picker__option"
                          tabindex="-1"
                          role="option"
                          aria-selected="{{ (string) $selectedStudentId === (string) $student->id ? 'true' : 'false' }}"
                          data-student-id="{{ $student->id }}"
                          data-student-name="{{ $studentName }}"
                          data-student-dni="{{ $student->dni }}"
                          data-student-grade="{{ $student->grade }}"
                        >
                          <span class="enrollment-student-picker__avatar" aria-hidden="true">{{ $studentInitials }}</span>
                          <span class="enrollment-student-picker__option-text">
                            <strong>{{ $studentName }}</strong>
                            <span>DNI {{ $student->dni }}{{ $student->grade ? ' · Grado '.$student->grade : '' }}</span>
                          </span>
                          <i class="fas fa-check enrollment-student-picker__option-check" aria-hidden="true"></i>
                        </button>
                      </li>
                    @endforeach
                  </ul>
                  <p class="enrollment-student-picker__empty" data-enrollment-student-empty hidden>No hay alumnos que coincidan con la búsqueda.</p>
                </div>
              </div>
              <button class="enrollment-student-add" type="button" data-open-student-modal>
                <i class="fas fa-user-plus" aria-hidden="true"></i> Agregar alumno
              </button>
            </div>
            <p class="enrollment-form__notice" role="status" data-student-added-message hidden></p>
          @endif
          @error('student_id')<small id="enrollmentStudentError" class="enrollment-form__error enrollment-student-picker__error is-visible">{{ $message }}</small>@enderror
          @if(!$editing && $students->isEmpty())
            <small class="enrollment-form__help">No hay alumnos disponibles para matricular este año. <a href="{{ route('admin.students.create') }}">Registra primero su ficha de alumno</a> o elige otro año lectivo.</small>
          @endif
        </div>

        <div class="enrollment-form__field">
          <label for="enrollmentYear">Año lectivo <span class="enrollment-form__required" aria-hidden="true">*</span></label>
          <input id="enrollmentYear" name="academic_year" type="number" min="2000" max="2100" value="{{ old('academic_year', $academicYear) }}" required aria-describedby="@error('academic_year') enrollmentYearError @enderror" @error('academic_year') aria-invalid="true" @enderror />
          @error('academic_year')<small id="enrollmentYearError" class="enrollment-form__error">{{ $message }}</small>@enderror
        </div>

        <div class="enrollment-form__field">
          <label for="enrollmentDate">Fecha de registro <span class="enrollment-form__required" aria-hidden="true">*</span></label>
          <input id="enrollmentDate" name="enrollment_date" type="date" value="{{ old('enrollment_date', $enrollment?->enrollment_date?->format('Y-m-d') ?? now()->toDateString()) }}" max="{{ now()->toDateString() }}" required aria-describedby="@error('enrollment_date') enrollmentDateError @enderror" @error('enrollment_date') aria-invalid="true" @enderror />
          @error('enrollment_date')<small id="enrollmentDateError" class="enrollment-form__error">{{ $message }}</small>@enderror
        </div>

        <div class="enrollment-form__field">
          <label for="enrollmentGrade">Grado <span class="enrollment-form__required" aria-hidden="true">*</span></label>
          <select id="enrollmentGrade" name="grade" required aria-describedby="@error('grade') enrollmentGradeError @enderror" @error('grade') aria-invalid="true" @enderror>
            <option value="">Selecciona un grado...</option>
            @foreach(\App\Models\CourseAssignment::GRADES as $availableGrade)
              <option value="{{ $availableGrade }}" @selected(old('grade', $enrollment?->grade ?? $students->firstWhere('id', (int) $selectedStudentId)?->grade) === $availableGrade)>{{ $availableGrade }}</option>
            @endforeach
          </select>
          @error('grade')<small id="enrollmentGradeError" class="enrollment-form__error">{{ $message }}</small>@enderror
        </div>

        <div class="enrollment-form__field">
          <label for="enrollmentSection">Sección</label>
          <input id="enrollmentSection" name="section" type="text" maxlength="20" value="{{ old('section', $enrollment?->section) }}" placeholder="Ej. A" class="enrollment-form__uppercase" data-uppercase aria-describedby="enrollmentSectionHelp @error('section') enrollmentSectionError @enderror" @error('section') aria-invalid="true" @enderror />
          <small id="enrollmentSectionHelp" class="enrollment-form__help">Opcional; se guardará en mayúsculas.</small>
          @error('section')<small id="enrollmentSectionError" class="enrollment-form__error">{{ $message }}</small>@enderror
        </div>
      </div>
    </section>

    <section class="enrollment-form__section" aria-labelledby="enrollmentGuardianSection">
      <h2 class="enrollment-form__section-title" id="enrollmentGuardianSection"><i class="fas fa-user-shield" aria-hidden="true"></i> Apoderado y contacto</h2>
      <div class="enrollment-form__grid">
        <div class="enrollment-form__field">
          <label for="guardianName">Nombre del apoderado <span class="enrollment-form__required" aria-hidden="true">*</span></label>
          <input id="guardianName" name="guardian_name" type="text" maxlength="180" value="{{ old('guardian_name', $enrollment?->guardian_name) }}" autocomplete="name" class="enrollment-form__uppercase" data-uppercase required aria-describedby="@error('guardian_name') guardianNameError @enderror" @error('guardian_name') aria-invalid="true" @enderror />
          @error('guardian_name')<small id="guardianNameError" class="enrollment-form__error">{{ $message }}</small>@enderror
        </div>

        <div class="enrollment-form__field">
          <label for="guardianRelationship">Parentesco <span class="enrollment-form__required" aria-hidden="true">*</span></label>
          <select id="guardianRelationship" name="guardian_relationship" required aria-describedby="@error('guardian_relationship') guardianRelationshipError @enderror" @error('guardian_relationship') aria-invalid="true" @enderror>
            <option value="">Selecciona...</option>
            @foreach(\App\Models\Enrollment::GUARDIAN_RELATIONSHIPS as $relationship)
              <option value="{{ $relationship }}" @selected(old('guardian_relationship', $enrollment?->guardian_relationship) === $relationship)>{{ $relationship }}</option>
            @endforeach
          </select>
          @error('guardian_relationship')<small id="guardianRelationshipError" class="enrollment-form__error">{{ $message }}</small>@enderror
        </div>

        <div class="enrollment-form__field">
          <label for="guardianPhone">Teléfono de contacto</label>
          <input id="guardianPhone" name="guardian_phone" type="tel" maxlength="40" value="{{ old('guardian_phone', $enrollment?->guardian_phone) }}" autocomplete="tel" />
          <small class="enrollment-form__help">Incluye código de área si corresponde.</small>
        </div>

        <div class="enrollment-form__field">
          <label for="guardianEmail">Correo del apoderado</label>
          <input id="guardianEmail" name="guardian_email" type="email" maxlength="180" value="{{ old('guardian_email', $enrollment?->guardian_email) }}" autocomplete="email" @error('guardian_email') aria-invalid="true" aria-describedby="guardianEmailError" @enderror />
          @error('guardian_email')<small id="guardianEmailError" class="enrollment-form__error">{{ $message }}</small>@enderror
        </div>

        <div class="enrollment-form__field enrollment-form__field--wide">
          <label for="enrollmentAddress">Dirección</label>
          <input id="enrollmentAddress" name="address" type="text" maxlength="500" value="{{ old('address', $enrollment?->address) }}" autocomplete="street-address" @error('address') aria-invalid="true" aria-describedby="enrollmentAddressError" @enderror />
          @error('address')<small id="enrollmentAddressError" class="enrollment-form__error">{{ $message }}</small>@enderror
        </div>
      </div>
    </section>

    <section class="enrollment-form__section" aria-labelledby="enrollmentNotesSection">
      <h2 class="enrollment-form__section-title" id="enrollmentNotesSection"><i class="fas fa-note-sticky" aria-hidden="true"></i> Observaciones</h2>
      <div class="enrollment-form__field">
        <label for="enrollmentNotes">Notas administrativas</label>
        <textarea id="enrollmentNotes" name="notes" maxlength="2000" placeholder="Información relevante para la gestión de la matrícula..." @error('notes') aria-invalid="true" aria-describedby="enrollmentNotesError" @enderror>{{ old('notes', $enrollment?->notes) }}</textarea>
        @error('notes')<small id="enrollmentNotesError" class="enrollment-form__error">{{ $message }}</small>@enderror
      </div>
    </section>

    @if(!$editing)
      <p class="enrollment-form__notice" role="note">La ficha se creará en estado <strong>borrador</strong>. Podrás revisarla, imprimirla y confirmar la matrícula desde su ficha.</p>
    @endif

    <footer class="enrollment-form__footer">
      <span class="enrollment-form__footer-note"><span class="enrollment-form__required">*</span> Campos obligatorios. No se solicitan datos médicos ni documentos personales.</span>
      <div class="enrollment-form__actions">
        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-dark"><i class="fas fa-arrow-left" aria-hidden="true"></i> Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check" aria-hidden="true"></i> {{ $submitLabel }}</button>
      </div>
    </footer>
  </form>
</section>

@if(!$editing)
  <dialog class="enrollment-student-modal" id="addStudentModal" aria-labelledby="addStudentModalTitle" aria-describedby="addStudentModalDescription">
    <div class="enrollment-student-modal__content">
      <header class="enrollment-student-modal__header">
        <span class="enrollment-student-modal__icon" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
        <div class="enrollment-student-modal__heading">
          <span class="enrollment-student-modal__eyebrow">Registro rápido</span>
          <h2 id="addStudentModalTitle">Agregar alumno</h2>
          <p id="addStudentModalDescription">Completa los datos básicos. Al guardar, lo seleccionaremos en esta matrícula.</p>
        </div>
        <button class="enrollment-student-modal__close" type="button" aria-label="Cerrar ventana" data-close-student-modal>
          <i class="fas fa-times" aria-hidden="true"></i>
        </button>
      </header>

      <form
        class="enrollment-student-modal__form"
        id="addStudentForm"
        method="POST"
        action="{{ route('admin.enrollments.students.store') }}"
        data-student-store-url="{{ route('admin.enrollments.students.store') }}"
      >
        @csrf
        <div class="enrollment-student-modal__grid">
          <div class="enrollment-student-modal__field enrollment-student-modal__field--wide">
            <label for="quickStudentDni">DNI / documento <span class="enrollment-form__required" aria-hidden="true">*</span></label>
            <input id="quickStudentDni" name="dni" type="text" maxlength="50" autocomplete="off" required data-uppercase />
          </div>
          <div class="enrollment-student-modal__field">
            <label for="quickStudentFirstName">Nombres <span class="enrollment-form__required" aria-hidden="true">*</span></label>
            <input id="quickStudentFirstName" name="first_name" type="text" maxlength="120" autocomplete="given-name" required data-uppercase />
          </div>
          <div class="enrollment-student-modal__field">
            <label for="quickStudentLastName">Apellidos <span class="enrollment-form__required" aria-hidden="true">*</span></label>
            <input id="quickStudentLastName" name="last_name" type="text" maxlength="120" autocomplete="family-name" required data-uppercase />
          </div>
          <div class="enrollment-student-modal__field">
            <label for="quickStudentBirthDate">Fecha de nacimiento</label>
            <input id="quickStudentBirthDate" name="birth_date" type="date" max="{{ now()->toDateString() }}" />
          </div>
          <div class="enrollment-student-modal__field">
            <label for="quickStudentGrade">Grado actual</label>
            <select id="quickStudentGrade" name="grade">
              <option value="">Sin especificar</option>
              @foreach(\App\Models\CourseAssignment::GRADES as $availableGrade)
                <option value="{{ $availableGrade }}">{{ $availableGrade }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <p class="enrollment-student-modal__hint">El DNI debe ser único. Si el alumno ya cuenta con una ficha, selecciónala desde la búsqueda en lugar de crear otra.</p>
        <div class="enrollment-student-modal__message" role="alert" aria-live="polite" data-student-modal-message></div>
        <footer class="enrollment-student-modal__actions">
          <button type="button" data-close-student-modal><i class="fas fa-arrow-left" aria-hidden="true"></i> Cancelar</button>
          <button type="submit" data-student-modal-submit>
            <i class="fas fa-user-check" aria-hidden="true"></i><span data-student-modal-submit-label>Guardar y seleccionar</span>
          </button>
        </footer>
      </form>
    </div>
  </dialog>
@endif

@push('scripts')
  <script>
    document.querySelectorAll('[data-enrollment-student-picker]').forEach(picker => {
      const trigger = picker.querySelector('[data-enrollment-student-trigger]');
      const studentValue = picker.querySelector('[data-enrollment-student-value]');
      const panel = picker.querySelector('[data-enrollment-student-panel]');
      const search = picker.querySelector('[data-enrollment-student-search]');
      const count = picker.querySelector('[data-enrollment-student-count]');
      const empty = picker.querySelector('[data-enrollment-student-empty]');
      const error = document.getElementById('enrollmentStudentError');
      let options = Array.from(picker.querySelectorAll('[data-enrollment-student-item]'));
      const form = picker.closest('form');
      const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es-PE').trim();
      const visibleOptions = () => options.filter(item => !item.hidden);
      const filterOptions = () => {
        const query = normalize(search.value);
        let visibleCount = 0;

        options.forEach(item => {
          const option = item.querySelector('[role="option"]');
          const searchText = normalize(`${option.dataset.studentName} ${option.dataset.studentDni}`);
          item.hidden = query !== '' && !searchText.includes(query);
          if (!item.hidden) visibleCount += 1;
        });

        count.textContent = query === ''
          ? `${visibleCount} ${visibleCount === 1 ? 'alumno disponible' : 'alumnos disponibles'}`
          : `${visibleCount} ${visibleCount === 1 ? 'resultado' : 'resultados'}`;
        empty.hidden = visibleCount > 0;
      };
      const close = returnFocus => {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        if (returnFocus) trigger.focus();
      };
      const open = () => {
        panel.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        search.value = '';
        filterOptions();
        search.focus();
      };
      const selectOption = option => {
        const name = option.dataset.studentName;
        const dni = option.dataset.studentDni;
        const initials = option.querySelector('.enrollment-student-picker__avatar').textContent;
        const avatar = document.createElement('span');
        avatar.className = 'enrollment-student-picker__avatar';
        avatar.setAttribute('aria-hidden', 'true');
        avatar.textContent = initials;

        const copy = document.createElement('span');
        copy.className = 'enrollment-student-picker__copy';
        const studentName = document.createElement('strong');
        studentName.textContent = name;
        const studentDni = document.createElement('span');
        studentDni.textContent = `DNI ${dni}`;
        copy.append(studentName, studentDni);

        const chevron = document.createElement('i');
        chevron.className = 'fas fa-chevron-down enrollment-student-picker__chevron';
        chevron.setAttribute('aria-hidden', 'true');
        trigger.replaceChildren(avatar, copy, chevron);
        studentValue.value = option.dataset.studentId;
        trigger.removeAttribute('aria-invalid');
        error?.classList.remove('is-visible');
        options.forEach(item => {
          item.querySelector('[role="option"]').setAttribute(
            'aria-selected',
            item.querySelector('[role="option"]') === option ? 'true' : 'false'
          );
        });

        const grade = document.getElementById('enrollmentGrade');
        if (grade) grade.value = option.dataset.studentGrade || '';
        close(true);
      };
      const wireOption = item => {
        const option = item.querySelector('[role="option"]');
        option.addEventListener('click', () => selectOption(option));
        option.addEventListener('keydown', event => {
          const visible = visibleOptions().map(visibleItem => visibleItem.querySelector('[role="option"]'));
          const currentIndex = visible.indexOf(option);
          if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const direction = event.key === 'ArrowDown' ? 1 : -1;
            visible[(currentIndex + direction + visible.length) % visible.length]?.focus();
          } else if (event.key === 'Escape') {
            event.preventDefault();
            close(true);
          } else if (event.key === 'Tab') {
            close(false);
          }
        });
      };
      const addStudentOption = student => {
        const studentName = `${student.last_name}, ${student.first_name}`;
        const initials = `${student.first_name.charAt(0)}${student.last_name.charAt(0)}`.toLocaleUpperCase('es-PE');
        const item = document.createElement('li');
        item.dataset.enrollmentStudentItem = '';

        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'enrollment-student-picker__option';
        option.tabIndex = -1;
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', 'false');
        option.dataset.studentId = student.id;
        option.dataset.studentName = studentName;
        option.dataset.studentDni = student.dni;
        option.dataset.studentGrade = student.grade || '';

        const avatar = document.createElement('span');
        avatar.className = 'enrollment-student-picker__avatar';
        avatar.setAttribute('aria-hidden', 'true');
        avatar.textContent = initials;

        const copy = document.createElement('span');
        copy.className = 'enrollment-student-picker__option-text';
        const name = document.createElement('strong');
        name.textContent = studentName;
        const details = document.createElement('span');
        details.textContent = `DNI ${student.dni}${student.grade ? ` · Grado ${student.grade}` : ''}`;
        copy.append(name, details);

        const check = document.createElement('i');
        check.className = 'fas fa-check enrollment-student-picker__option-check';
        check.setAttribute('aria-hidden', 'true');
        option.append(avatar, copy, check);
        item.append(option);
        picker.querySelector('[role="listbox"]').append(item);
        options = [...options, item];
        wireOption(item);
        filterOptions();

        return option;
      };

      trigger.addEventListener('click', () => {
        if (panel.hidden) open();
        else close(false);
      });
      trigger.addEventListener('keydown', event => {
        if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          open();
        }
      });
      search.addEventListener('input', filterOptions);
      search.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
          event.preventDefault();
          close(true);
          return;
        }
        if (event.key === 'Enter') {
          event.preventDefault();
          const firstOption = visibleOptions()[0]?.querySelector('[role="option"]');
          if (firstOption) selectOption(firstOption);
          return;
        }
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          visibleOptions()[0]?.querySelector('[role="option"]').focus();
        }
      });

      options.forEach(wireOption);

      form?.addEventListener('submit', event => {
        if (studentValue.value) return;

        event.preventDefault();
        trigger.setAttribute('aria-invalid', 'true');
        if (error) {
          error.textContent = 'Selecciona un alumno para continuar.';
          error.classList.add('is-visible');
        }
        if (panel.hidden) open();
        else search.focus();
      });

      document.addEventListener('click', event => {
        if (!picker.contains(event.target) && !panel.hidden) close(false);
      });

      const dialog = document.getElementById('addStudentModal');
      const addStudentButton = document.querySelector('[data-open-student-modal]');
      const closeModalButtons = dialog?.querySelectorAll('[data-close-student-modal]');
      const modalForm = document.getElementById('addStudentForm');
      const modalMessage = dialog?.querySelector('[data-student-modal-message]');
      const modalSubmit = dialog?.querySelector('[data-student-modal-submit]');
      const modalSubmitLabel = dialog?.querySelector('[data-student-modal-submit-label]');
      const addedStudentMessage = document.querySelector('[data-student-added-message]');
      let returnFocusTarget = addStudentButton;

      addStudentButton?.addEventListener('click', () => {
        returnFocusTarget = addStudentButton;
        modalMessage.textContent = '';
        dialog.showModal();
        dialog.querySelector('[name="dni"]').focus();
      });
      closeModalButtons?.forEach(button => button.addEventListener('click', () => dialog.close()));
      dialog?.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
      });
      dialog?.addEventListener('close', () => returnFocusTarget?.focus());

      modalForm?.addEventListener('submit', async event => {
        event.preventDefault();
        modalMessage.textContent = '';
        modalSubmit.disabled = true;
        modalSubmit.setAttribute('aria-busy', 'true');
        modalSubmitLabel.textContent = 'Guardando alumno...';

        try {
          const response = await fetch(modalForm.dataset.studentStoreUrl, {
            method: 'POST',
            body: new FormData(modalForm),
            credentials: 'same-origin',
            headers: {
              Accept: 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          });
          const result = await response.json().catch(() => null);

          if (!response.ok) {
            const validationMessage = result?.errors
              ? Object.values(result.errors).flat().join(' ')
              : '';
            modalMessage.textContent = validationMessage || result?.message || 'No se pudo guardar el alumno. Intenta nuevamente.';
            return;
          }

          const option = addStudentOption(result.student);
          modalForm.reset();
          dialog.close();
          selectOption(option);
          addedStudentMessage.textContent = result.message;
          addedStudentMessage.hidden = false;
        } catch (requestError) {
          modalMessage.textContent = 'No fue posible conectar con el servidor. Revisa tu conexión e intenta nuevamente.';
        } finally {
          modalSubmit.disabled = false;
          modalSubmit.removeAttribute('aria-busy');
          modalSubmitLabel.textContent = 'Guardar y seleccionar';
        }
      });
    });

    document.querySelectorAll('[data-uppercase]').forEach(input => {
      input.addEventListener('input', () => {
        const cursor = input.selectionStart;
        input.value = input.value.toLocaleUpperCase('es-PE');
        if (cursor !== null) input.setSelectionRange(cursor, cursor);
      });
    });
  </script>
@endpush
