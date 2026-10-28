@php
  $student = $student ?? null;
  $editing = $editing ?? false;
  $studentUsers = $studentUsers ?? collect();
@endphp

@push('styles')
  <style>
    .student-form-card {
      max-width: 780px;
      margin: 0 auto;
      padding: clamp(1rem, 2vw, 1.6rem) !important;
      border: 1px solid #e4ebe5 !important;
      border-radius: 20px !important;
      background: linear-gradient(150deg, #fff, #fbfdfb) !important;
      box-shadow: 0 16px 40px rgba(20, 52, 34, .065) !important;
    }

    .student-form__header {
      display: flex;
      align-items: flex-start;
      gap: .85rem;
      margin-bottom: 1.15rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #e8eee8;
    }

    .student-form__icon {
      display: grid;
      width: 42px;
      height: 42px;
      flex: 0 0 42px;
      place-items: center;
      border: 1px solid #deebdf;
      border-radius: 13px;
      background: linear-gradient(145deg, #edf6ee, #f7f3e9);
      color: #37704a;
      font-size: 1rem;
    }

    .student-form__eyebrow {
      color: #62816c;
      font-size: .66rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    .student-form__header h1 {
      margin: .2rem 0 .25rem;
      color: #183b29;
      font-size: clamp(1.4rem, 2.7vw, 1.8rem);
      letter-spacing: -.035em;
    }

    .student-form__header p {
      max-width: 520px;
      margin: 0;
      color: #68776c;
      font-size: .8rem;
      line-height: 1.55;
    }

    .student-form__alerts {
      display: grid;
      gap: .5rem;
      margin-bottom: 1rem;
    }

    .student-form__alert {
      padding: .68rem .85rem;
      border: 1px solid #f0d0cc;
      border-radius: 10px;
      background: #fff5f3;
      color: #923d32;
      font-size: .76rem;
    }

    .student-form__grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .9rem;
    }

    .student-form__field--wide {
      grid-column: 1 / -1;
    }

    .student-form__field {
      min-width: 0;
    }

    .student-form__field label {
      display: block;
      margin-bottom: .35rem;
      color: #304736;
      font-size: .74rem;
      font-weight: 750;
    }

    .student-form__required {
      color: #a64338;
    }

    .student-form__field input,
    .student-form__field select {
      display: block;
      width: 100%;
      min-height: 42px;
      padding: .6rem .72rem;
      border: 1px solid #dfe7df;
      border-radius: 10px;
      background: #fff;
      color: #263c2d;
      font: inherit;
      font-size: .78rem;
      transition: border-color .16s ease, box-shadow .16s ease;
    }

    .student-form__field input::placeholder {
      color: #9aa69d;
    }

    .student-form__uppercase {
      text-transform: uppercase;
    }

    .student-form__field input:focus,
    .student-form__field select:focus {
      outline: 0;
      border-color: #8daf93;
      box-shadow: 0 0 0 3px rgba(23, 104, 62, .12);
    }

    .student-form__field input[aria-invalid="true"],
    .student-form__field select[aria-invalid="true"] {
      border-color: #cf8179;
    }

    .student-form__help,
    .student-form__error {
      display: block;
      margin-top: .32rem;
      font-size: .67rem;
      line-height: 1.45;
    }

    .student-form__help {
      color: #748177;
    }

    .student-form__error {
      color: #a43e34;
      font-weight: 650;
    }

    .student-form__footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .7rem;
      margin-top: 1.25rem;
      padding-top: .9rem;
      border-top: 1px solid #e8eee8;
    }

    .student-form__footer-note {
      color: #748177;
      font-size: .68rem;
    }

    .student-form__actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: .5rem;
    }

    .student-form__actions .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      min-height: 40px !important;
      padding: .6rem .85rem !important;
      border-radius: 9px !important;
      font-size: .74rem !important;
      font-weight: 750 !important;
      text-decoration: none;
    }

    @media (max-width: 600px) {
      .student-form__grid {
        grid-template-columns: minmax(0, 1fr);
        gap: .75rem;
      }

      .student-form__footer {
        align-items: stretch;
        flex-direction: column;
      }

      .student-form__actions {
        width: 100%;
      }

      .student-form__actions .btn {
        flex: 1;
      }
    }
  </style>
@endpush

<section class="admin-card student-form-card" aria-labelledby="studentFormTitle">
  <header class="student-form__header">
    <span class="student-form__icon"><i class="fas {{ $editing ? 'fa-user-pen' : 'fa-user-plus' }}" aria-hidden="true"></i></span>
    <div>
      <span class="student-form__eyebrow">Directorio de alumnos</span>
      <h1 id="studentFormTitle">{{ $formTitle }}</h1>
      <p>{{ $formDescription }}</p>
    </div>
  </header>

  @if($errors->any())
    <div class="student-form__alerts" role="alert">
      @foreach($errors->all() as $error)
        <div class="student-form__alert">{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ $formAction }}" class="student-form">
    @csrf
    @if($editing)
      @method('PUT')
    @endif

    <div class="student-form__grid">
      <div class="student-form__field student-form__field--wide">
        <label for="studentUser">Cuenta de acceso</label>
        <select id="studentUser" name="user_id" aria-describedby="studentUserHelp @error('user_id') studentUserError @enderror" @error('user_id') aria-invalid="true" @enderror>
          <option value="">Sin cuenta vinculada</option>
          @foreach($studentUsers as $studentUser)
            <option value="{{ $studentUser->id }}" @selected((string) old('user_id', $selectedUserId ?? $student?->user_id) === (string) $studentUser->id)>
              {{ $studentUser->name }} — {{ $studentUser->email }}
            </option>
          @endforeach
        </select>
        <small id="studentUserHelp" class="student-form__help">Vincula la cuenta que inicia sesión como alumno. La ficha de matrícula conserva sus propios datos de DNI, nombres y grado.</small>
        @error('user_id')<small id="studentUserError" class="student-form__error">{{ $message }}</small>@enderror
      </div>

      <div class="student-form__field">
        <label for="studentDni">DNI <span class="student-form__required" aria-hidden="true">*</span></label>
        <input id="studentDni" name="dni" type="text" value="{{ old('dni', $student?->dni) }}" maxlength="50" autocomplete="off" class="student-form__uppercase" data-uppercase required aria-describedby="studentDniHelp @error('dni') studentDniError @enderror" @error('dni') aria-invalid="true" @enderror />
        <small id="studentDniHelp" class="student-form__help">Identificador único del alumno.</small>
        @error('dni')<small id="studentDniError" class="student-form__error">{{ $message }}</small>@enderror
      </div>

      <div class="student-form__field">
        <label for="studentGrade">Grado</label>
        <select id="studentGrade" name="grade" aria-describedby="studentGradeHelp @error('grade') studentGradeError @enderror" @error('grade') aria-invalid="true" @enderror>
          <option value="">Selecciona un grado</option>
          @foreach(\App\Models\CourseAssignment::GRADES as $availableGrade)
            <option value="{{ $availableGrade }}" @selected(old('grade', $student?->grade) === $availableGrade)>{{ $availableGrade }}</option>
          @endforeach
        </select>
        <small id="studentGradeHelp" class="student-form__help">Elige uno de los grados registrados en el sistema. Este campo es opcional.</small>
        @error('grade')<small id="studentGradeError" class="student-form__error">{{ $message }}</small>@enderror
      </div>

      <div class="student-form__field">
        <label for="studentLastName">Apellidos <span class="student-form__required" aria-hidden="true">*</span></label>
        <input id="studentLastName" name="last_name" type="text" value="{{ old('last_name', $student?->last_name) }}" maxlength="120" autocomplete="family-name" class="student-form__uppercase" data-uppercase required @error('last_name') aria-invalid="true" aria-describedby="studentLastNameError" @enderror />
        @error('last_name')<small id="studentLastNameError" class="student-form__error">{{ $message }}</small>@enderror
      </div>

      <div class="student-form__field">
        <label for="studentFirstName">Nombres <span class="student-form__required" aria-hidden="true">*</span></label>
        <input id="studentFirstName" name="first_name" type="text" value="{{ old('first_name', $student?->first_name) }}" maxlength="120" autocomplete="given-name" class="student-form__uppercase" data-uppercase required @error('first_name') aria-invalid="true" aria-describedby="studentFirstNameError" @enderror />
        @error('first_name')<small id="studentFirstNameError" class="student-form__error">{{ $message }}</small>@enderror
      </div>

      <div class="student-form__field">
        <label for="studentBirthDate">Fecha de nacimiento</label>
        <input id="studentBirthDate" name="birth_date" type="date" value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}" autocomplete="bday" aria-describedby="studentBirthDateHelp @error('birth_date') studentBirthDateError @enderror" @error('birth_date') aria-invalid="true" @enderror />
        <small id="studentBirthDateHelp" class="student-form__help">Opcional; aparecerá en la ficha de matrícula.</small>
        @error('birth_date')<small id="studentBirthDateError" class="student-form__error">{{ $message }}</small>@enderror
      </div>
    </div>

    <footer class="student-form__footer">
      <span class="student-form__footer-note"><span class="student-form__required">*</span> Campos obligatorios</span>
      <div class="student-form__actions">
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-dark">Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check" aria-hidden="true"></i> {{ $submitLabel }}</button>
      </div>
    </footer>
  </form>
</section>

@push('scripts')
  <script>
    document.querySelectorAll('[data-uppercase]').forEach(input => {
      const uppercaseValue = () => {
        const cursor = input.selectionStart;
        input.value = input.value.toLocaleUpperCase('es-PE');
        if (cursor !== null) input.setSelectionRange(cursor, cursor);
      };

      uppercaseValue();
      input.addEventListener('input', uppercaseValue);
    });
  </script>
@endpush
