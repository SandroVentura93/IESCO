@extends('layouts.admin')

@php
  $adminSection = 'courses';
@endphp

@push('styles')
  <style>
    .course-form-page {
      --course-green: #17683e;
      width: min(100%, 760px);
      padding: clamp(1.15rem, 3vw, 2rem) !important;
      border: 1px solid #e6ece6 !important;
      border-radius: 20px !important;
      background: linear-gradient(150deg, #fff, #fbfdfb) !important;
      box-shadow: 0 16px 40px rgba(20, 52, 34, .07) !important;
    }

    .course-form-page__eyebrow {
      color: #62816c;
      font-size: .7rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    .course-form-page h1 {
      margin: .4rem 0;
      color: #183b29;
      font-size: clamp(1.55rem, 3vw, 2rem);
    }

    .course-form-page__description {
      margin-bottom: 1.25rem;
      color: #68776c;
      font-size: .85rem;
    }

    .course-form-page__field {
      display: grid;
      gap: .45rem;
    }

    .course-form-page__field label {
      color: #334b3a;
      font-size: .78rem;
      font-weight: 750;
    }

    .course-form-page__field input {
      width: 100%;
      min-height: 46px;
      padding: .65rem .75rem;
      border: 1px solid #d9e3da;
      border-radius: 10px;
      background: #fff;
      color: #243a2b;
      font: inherit;
      font-size: .85rem;
    }

    .course-form-page__field input:focus {
      outline: 3px solid rgba(23, 104, 62, .14);
      border-color: #17683e;
    }

    .course-form-page__help {
      color: #718075;
      font-size: .72rem;
    }

    .course-form-page__error,
    .course-form-page__alerts {
      border: 1px solid #f0d0cc;
      border-radius: 10px;
      background: #fff5f3;
      color: #9c3d32;
    }

    .course-form-page__error {
      padding: .55rem .7rem;
      font-size: .75rem;
    }

    .course-form-page__alerts {
      margin-bottom: 1rem;
      padding: .8rem 1rem;
      font-size: .8rem;
    }

    .course-form-page__actions {
      display: flex;
      justify-content: flex-end;
      gap: .55rem;
      margin-top: 1.2rem;
      padding-top: 1rem;
      border-top: 1px solid #edf1ed;
    }

    .course-form-page__actions .btn {
      min-height: 40px;
      border-radius: 9px;
      font-size: .78rem;
      font-weight: 750;
    }

    @media (max-width: 520px) {
      .course-form-page__actions {
        flex-direction: column-reverse;
      }

      .course-form-page__actions .btn {
        justify-content: center;
        width: 100%;
      }
    }
  </style>
@endpush

@section('admin_content')
  <section class="admin-card admin-courses course-form-page" aria-labelledby="courseEditTitle">
    <span class="course-form-page__eyebrow"><i class="fas fa-graduation-cap" aria-hidden="true"></i> Catálogo académico</span>
    <h1 id="courseEditTitle">Editar curso</h1>
    <p class="course-form-page__description">Actualiza el nombre del curso. Las asignaciones y los recursos vinculados se conservarán.</p>

    @if($errors->any())
      <div class="course-form-page__alerts" role="alert">
        <strong>Revisa los datos del formulario:</strong>
        <ul>
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.courses.update', $c->id) }}" class="admin-form-stack">
      @csrf
      @method('PUT')
      <div class="course-form-page__field">
        <label for="courseName">Nombre del curso</label>
        <input id="courseName" name="name" type="text" value="{{ old('name', $c->name) }}" maxlength="255" required autofocus aria-describedby="courseNameHelp" />
        <span id="courseNameHelp" class="course-form-page__help">Usa el nombre oficial de la asignatura (máximo 255 caracteres).</span>
        @error('name')
          <span class="course-form-page__error" role="alert">{{ $message }}</span>
        @enderror
      </div>
      <div class="course-form-page__actions">
        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-dark">Cancelar</a>
        <button class="btn btn-primary" type="submit"><i class="fas fa-check" aria-hidden="true"></i> Guardar cambios</button>
      </div>
    </form>
  </section>
@endsection
