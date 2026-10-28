@extends('layouts.admin')

@php $adminSection = 'files-panel'; @endphp

@section('admin_content')
  <section class="file-edit-page">
    <header class="file-edit-heading">
      <a class="file-edit-back" href="{{ route('admin.files.panel') }}">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Volver a archivos
      </a>
      <div>
        <span class="file-edit-eyebrow">GESTIÓN DE CONTENIDO</span>
        <h1>Editar archivo</h1>
        <p>Actualiza los detalles, la ubicación o reemplaza el archivo sin perder el control de su publicación.</p>
      </div>
    </header>

    @if($errors->any())
      <div class="alert alert-error file-edit-errors" role="alert">
        <strong>No se pudieron guardar los cambios.</strong>
        <ul>
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="file-edit-layout">
      <aside class="file-edit-preview">
        <span class="file-edit-preview-label">ARCHIVO ACTUAL</span>
        <div class="file-edit-preview-media">
          @if($file->file_type === 'image')
            <img src="{{ route('admin.files.preview', $file->id) }}" alt="{{ $file->name }}" />
          @elseif($file->file_type === 'video')
            <video src="{{ route('admin.files.preview', $file->id) }}" muted controls preload="metadata"></video>
          @elseif($file->file_type === 'audio')
            <i class="fas fa-file-audio" aria-hidden="true"></i>
          @elseif($file->file_type === 'document')
            <i class="fas fa-file-lines" aria-hidden="true"></i>
          @else
            <i class="fas fa-file" aria-hidden="true"></i>
          @endif
        </div>
        <strong>{{ $file->name }}</strong>
        <span>{{ number_format($file->size / 1048576, 2) }} MB · {{ $file->mime ?: 'Tipo desconocido' }}</span>
        <a class="btn btn-outline-dark" href="{{ route('admin.files.download', $file->id) }}">
          <i class="fas fa-download" aria-hidden="true"></i>
          Descargar original
        </a>
      </aside>

      <form method="POST" action="{{ route('admin.files.update', $file->id) }}" enctype="multipart/form-data" class="file-edit-form">
        @csrf
        @method('PUT')

        <section class="file-edit-section">
          <div class="file-edit-section-heading">
            <span class="file-edit-step">01</span>
            <div>
              <h2>Información del archivo</h2>
              <p>Los detalles que identifican el recurso en el panel y en el sitio.</p>
            </div>
          </div>
          <div class="file-edit-fields">
            <label class="file-edit-field file-edit-field-full" for="edit-name">
              <span>Nombre del archivo</span>
              <input id="edit-name" name="name" type="text" value="{{ old('name', $file->name) }}" maxlength="255" required />
            </label>
            <label class="file-edit-field file-edit-field-full" for="edit-description">
              <span>Descripción <small>Opcional</small></span>
              <textarea id="edit-description" name="description" rows="4" maxlength="2000" placeholder="Añade una descripción útil para este recurso...">{{ old('description', $file->description) }}</textarea>
            </label>
            <label class="file-edit-field file-edit-field-full" for="edit-file">
              <span>Reemplazar archivo <small>Opcional</small></span>
              <input id="edit-file" name="file" type="file" accept="image/*,video/*,audio/*,application/*" />
              <small>Déjalo vacío para conservar el archivo actual. Tamaño máximo: 500 MB.</small>
            </label>
          </div>
        </section>

        <section class="file-edit-section">
          <div class="file-edit-section-heading">
            <span class="file-edit-step">02</span>
            <div>
              <h2>Organización y acceso</h2>
              <p>Define dónde aparece el recurso y quién puede consultarlo.</p>
            </div>
          </div>
          <div class="file-edit-fields">
            <label class="file-edit-field" for="edit-section">
              <span>Sección de destino</span>
              <select id="edit-section" name="section">
                <option value="" {{ old('section', $file->section) ? '' : 'selected' }}>Sin sección</option>
                @foreach($sectionOptions as $value => $label)
                  <option value="{{ $value }}" {{ old('section', $file->section) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </label>
            <div class="file-edit-field" id="edit-subfolder-group" hidden>
              <label for="edit-subfolder">Carpeta de grado</label>
              <select id="edit-subfolder" name="subfolder" disabled>
                <option value="">Selecciona un grado</option>
              </select>
            </div>
            <div class="file-edit-field" id="edit-course-group" hidden>
              <label for="edit-course">Curso</label>
              <select id="edit-course" name="course_id" disabled>
                <option value="">Selecciona un curso</option>
                @foreach($courses as $course)
                  <option value="{{ $course['id'] }}" {{ (string) old('course_id', $courseId) === (string) $course['id'] ? 'selected' : '' }}>{{ $course['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="file-edit-field" id="edit-student-group" hidden>
              <label for="edit-student">Alumno <small>Opcional</small></label>
              <select id="edit-student" name="student_id" disabled>
                <option value="">Sin alumno asignado</option>
                @foreach($students as $student)
                  <option value="{{ $student->id }}" {{ (string) old('student_id', $file->student_id) === (string) $student->id ? 'selected' : '' }}>
                    {{ $student->last_name }}, {{ $student->first_name }}{{ $student->grade ? ' · '.$student->grade : '' }}
                  </option>
                @endforeach
              </select>
            </div>
            <label class="file-edit-field" for="edit-grade">
              <span>Grado académico <small>Opcional</small></span>
              <select id="edit-grade" name="grade">
                <option value="">No especificado</option>
                @foreach(['1°', '2°', '3°', '4°', '5°', '6°', 'Otro'] as $grade)
                  <option value="{{ $grade }}" {{ old('grade', $file->grade) === $grade ? 'selected' : '' }}>{{ $grade }}</option>
                @endforeach
              </select>
            </label>
            <label class="file-edit-field" for="edit-file-type">
              <span>Tipo de archivo</span>
              <select id="edit-file-type" name="file_type" required>
                @foreach(['image' => 'Imagen', 'document' => 'Documento', 'video' => 'Video', 'audio' => 'Audio', 'other' => 'Otro'] as $value => $label)
                  <option value="{{ $value }}" {{ old('file_type', $file->file_type ?: 'other') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </label>
            <label class="file-edit-field" for="edit-visibility">
              <span>Visibilidad</span>
              <select id="edit-visibility" name="visibility" required>
                <option value="published" {{ old('visibility', $file->visibility) === 'published' ? 'selected' : '' }}>Publicado · visible en el sitio</option>
                <option value="private" {{ old('visibility', $file->visibility) === 'private' ? 'selected' : '' }}>Privado · solo administración</option>
              </select>
            </label>
          </div>
        </section>

        <section class="file-edit-section">
          <div class="file-edit-section-heading">
            <span class="file-edit-step">03</span>
            <div>
              <h2>Disponibilidad</h2>
              <p>Programa cuándo estará visible. Deja las fechas vacías para no limitarlo.</p>
            </div>
          </div>
          <div class="file-edit-fields">
            <label class="file-edit-field" for="edit-available-from">
              <span>Mostrar desde</span>
              <input id="edit-available-from" type="date" name="available_from" value="{{ old('available_from', $file->available_from?->format('Y-m-d')) }}" />
            </label>
            <label class="file-edit-field" for="edit-available-until">
              <span>Mostrar hasta</span>
              <input id="edit-available-until" type="date" name="available_until" value="{{ old('available_until', $file->available_until?->format('Y-m-d')) }}" />
            </label>
          </div>
        </section>

        <footer class="file-edit-actions">
          <a class="btn btn-outline-dark" href="{{ route('admin.files.panel') }}">Cancelar</a>
          <button class="btn btn-primary" type="submit">
            <i class="fas fa-check" aria-hidden="true"></i>
            Guardar cambios
          </button>
        </footer>
      </form>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const sectionSelect = document.getElementById('edit-section');
      const subfolderGroup = document.getElementById('edit-subfolder-group');
      const subfolderSelect = document.getElementById('edit-subfolder');
      const courseGroup = document.getElementById('edit-course-group');
      const courseSelect = document.getElementById('edit-course');
      const studentGroup = document.getElementById('edit-student-group');
      const studentSelect = document.getElementById('edit-student');
      const subfoldersBySection = @json($subfolderOptions);
      const courseHierarchySections = new Set(@json($courseHierarchySections));
      const selectedSubfolder = @json(old('subfolder', $subfolder));
      const selectedCourse = @json(old('course_id', $courseId));

      const courseExists = id => Array.from(courseSelect.options).some(option => option.value === String(id));

      const updateLocationFields = (initial = false) => {
        const currentSubfolder = initial ? selectedSubfolder : subfolderSelect.value;
        const currentCourse = initial ? selectedCourse : courseSelect.value;
        const subfolders = subfoldersBySection[sectionSelect.value] ?? {};
        subfolderSelect.replaceChildren(new Option('Selecciona un grado', ''));
        Object.entries(subfolders).forEach(([value, label]) => {
          subfolderSelect.add(new Option(label, value));
        });
        subfolderGroup.hidden = Object.keys(subfolders).length === 0;
        subfolderSelect.disabled = subfolderGroup.hidden;
        if (Object.hasOwn(subfolders, currentSubfolder)) {
          subfolderSelect.value = currentSubfolder;
        }

        const isActivitiesSection = sectionSelect.value === 'Actividades_Extracurriculares';
        const requiresCourse = courseHierarchySections.has(sectionSelect.value)
          && subfolderSelect.value !== '';
        courseGroup.hidden = !requiresCourse;
        courseSelect.disabled = !requiresCourse;
        courseSelect.required = requiresCourse;
        if (requiresCourse && courseExists(currentCourse)) {
          courseSelect.value = currentCourse;
        } else if (!requiresCourse) {
          courseSelect.value = '';
        }
        studentGroup.hidden = !isActivitiesSection;
        studentSelect.disabled = !isActivitiesSection;
      };

      sectionSelect.addEventListener('change', () => updateLocationFields());
      subfolderSelect.addEventListener('change', () => updateLocationFields());
      updateLocationFields(true);
    });
  </script>
@endpush
