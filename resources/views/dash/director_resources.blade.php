@extends('layouts.admin')

@php($adminSection = 'director-resources')

@section('admin_title', 'Recursos pedagógicos')
@section('admin_subtitle', 'Organiza los archivos por carpeta, grado y curso como en el administrador.')

@push('styles')
  <style>
    .director-resource-details {
      margin-top: .45rem;
      font-size: .82rem;
    }

    .director-resource-details summary {
      color: #276644;
      cursor: pointer;
      font-weight: 700;
    }

    .director-resource-details dl {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .45rem .75rem;
      margin: .55rem 0 0;
    }

    .director-resource-details dl div {
      min-width: 0;
    }

    .director-resource-details dt {
      color: #69776d;
      font-size: .68rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .director-resource-details dd {
      margin: .1rem 0 0;
      overflow-wrap: anywhere;
    }

    @media (max-width: 720px) {
      .director-resource-details dl {
        grid-template-columns: 1fr;
      }
    }
  </style>
@endpush

@section('admin_content')
  @if(session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-error" role="alert">
      <ul>
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="admin-card" aria-labelledby="directorResourceUploadTitle">
    <h3 id="directorResourceUploadTitle">Subir archivo a una carpeta</h3>
    <p class="muted-note">Los archivos publicados quedan disponibles en el sitio; los privados permanecen protegidos. La ubicación exacta aparece en la lista después de subirlos.</p>
    <form method="POST" action="{{ route('director.resources.store') }}" enctype="multipart/form-data" class="admin-form-stack">
      @csrf
      <label for="resourceFile">Archivo (máximo 50 MB)</label>
      <input id="resourceFile" name="file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.mp4,.mp3,.zip" required />

      <label for="resourceSection">Guardar en carpeta</label>
      <select id="resourceSection" name="section" required>
        <option value="" disabled {{ old('section') ? '' : 'selected' }}>Selecciona una carpeta</option>
        @foreach($sectionOptions as $value => $label)
          <option value="{{ $value }}" @selected(old('section') === $value)>{{ $label }}</option>
        @endforeach
      </select>

      <div id="resourceSubfolderGroup" hidden>
        <label for="resourceSubfolder">Subcarpeta / grado</label>
        <select id="resourceSubfolder" name="subfolder" disabled>
          <option value="">Selecciona un grado</option>
        </select>
      </div>

      <div id="resourceCourseGroup" hidden>
        <label for="resourceCourse">Curso</label>
        <select id="resourceCourse" name="course_id" disabled>
          <option value="">Selecciona un curso</option>
          @foreach($courses as $course)
            <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>{{ $course->name }}</option>
          @endforeach
        </select>
        @if($courses->isEmpty())
          <span class="admin-form-help">Primero registra un curso para guardar archivos en esta carpeta.</span>
        @endif
      </div>

      <div id="resourceWeekGroup" hidden>
        <label for="resourceWeek">Semana o unidad</label>
        <input id="resourceWeek" name="week" type="text" maxlength="50" value="{{ old('week') }}" placeholder="Ej. Semana 1" disabled />
      </div>

      <label for="resourceFileType">Tipo de archivo</label>
      <select id="resourceFileType" name="file_type" required>
        <option value="auto" @selected(old('file_type', 'auto') === 'auto')>Detectar automáticamente</option>
        <option value="image" @selected(old('file_type') === 'image')>Imagen</option>
        <option value="document" @selected(old('file_type') === 'document')>Documento</option>
        <option value="video" @selected(old('file_type') === 'video')>Video</option>
        <option value="audio" @selected(old('file_type') === 'audio')>Audio</option>
        <option value="other" @selected(old('file_type') === 'other')>Otro</option>
      </select>

      <label for="resourceDescription">Descripción (opcional)</label>
      <textarea id="resourceDescription" name="description" rows="3" maxlength="2000" placeholder="Agrega el propósito, contenido o indicaciones del archivo.">{{ old('description') }}</textarea>

      <label for="resourceVisibility">Visibilidad</label>
      <select id="resourceVisibility" name="visibility" required>
        <option value="published" @selected(old('visibility', 'published') === 'published')>Publicado (visible en el sitio)</option>
        <option value="private" @selected(old('visibility') === 'private')>Privado (solo administración)</option>
      </select>

      <div class="admin-actions-row">
        <button class="btn btn-primary" type="submit"><i class="fas fa-cloud-arrow-up" aria-hidden="true"></i> Subir archivo</button>
      </div>
    </form>
  </section>

  <section class="admin-card" aria-labelledby="directorResourceListTitle">
    <div class="admin-page-header">
      <div>
        <h3 id="directorResourceListTitle">Materiales registrados</h3>
        <p class="muted-note">{{ number_format($resources->total()) }} recursos pedagógicos</p>
      </div>
    </div>
    <div class="admin-table-wrap">
      <table class="table admin-table">
        <thead>
          <tr>
            <th scope="col">Recurso</th>
            <th scope="col">Curso</th>
            <th scope="col">Unidad</th>
            <th scope="col">Ubicación</th>
            <th scope="col">Registrado por</th>
            <th scope="col">Fecha</th>
            <th scope="col"><span class="sr-only">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($resources as $resource)
            <tr>
              <td data-label="Recurso">
                <a href="{{ asset('storage/'.$resource->path) }}" target="_blank" rel="noopener noreferrer">{{ $resource->name }}</a>
                <details class="director-resource-details" open>
                  <summary>Detalles del archivo</summary>
                  <dl>
                    <div><dt>Carpeta</dt><dd>Recursos Pedagógicos</dd></div>
                    <div><dt>Subcarpeta / grado</dt><dd>{{ \App\Support\StoredFileSections::subfolderFromPath('Recursos_Pedagogicos', $resource->path) ?? 'Sin subcarpeta' }}</dd></div>
                    <div><dt>Tipo</dt><dd>{{ $fileTypeOptions[$resource->storedFile?->file_type] ?? 'No especificado' }}</dd></div>
                    <div><dt>Formato</dt><dd>{{ $resource->mime ?: 'No especificado' }}</dd></div>
                    <div><dt>Tamaño</dt><dd>{{ $resource->size >= 1048576 ? number_format($resource->size / 1048576, 2).' MB' : number_format($resource->size / 1024, 1).' KB' }}</dd></div>
                    <div><dt>Descripción</dt><dd>{{ $resource->storedFile?->description ?: 'No se proporcionó descripción' }}</dd></div>
                    <div><dt>Visibilidad</dt><dd>{{ $resource->storedFile?->visibility === 'published' ? 'Publicado' : 'No disponible' }}</dd></div>
                    <div><dt>Registrado por</dt><dd>{{ $resource->user?->name ?? 'Usuario eliminado' }}</dd></div>
                    <div><dt>Fecha de carga</dt><dd>{{ $resource->created_at->format('d/m/Y H:i') }}</dd></div>
                  </dl>
                </details>
              </td>
              <td data-label="Curso">{{ $resource->course?->name ?? 'Curso eliminado' }}</td>
              <td data-label="Unidad">{{ $resource->week ?: 'Sin especificar' }}</td>
              <td data-label="Ubicación"><code>storage/app/public/{{ $resource->path }}</code></td>
              <td data-label="Registrado por">{{ $resource->user?->name ?? 'Usuario eliminado' }}</td>
              <td data-label="Fecha">{{ $resource->created_at->format('d/m/Y H:i') }}</td>
              <td class="admin-actions-cell">
                <form method="POST" action="{{ route('director.resources.destroy', $resource->id) }}" class="confirm-delete">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger" type="submit" aria-label="Eliminar {{ $resource->name }}"><i class="fas fa-trash" aria-hidden="true"></i> Eliminar</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="muted-note">Todavía no hay recursos pedagógicos cargados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $resources->links() }}
  </section>

  <section class="admin-card" aria-labelledby="directorOtherFilesTitle">
    <div class="admin-page-header">
      <div>
        <h3 id="directorOtherFilesTitle">Archivos de otras carpetas</h3>
        <p class="muted-note">{{ number_format($storedFiles->total()) }} archivos en actividades extracurriculares y plan estratégico</p>
      </div>
    </div>
    <div class="admin-table-wrap">
      <table class="table admin-table">
        <thead>
          <tr>
            <th scope="col">Archivo</th>
            <th scope="col">Carpeta</th>
            <th scope="col">Subcarpeta / grado</th>
            <th scope="col">Curso</th>
            <th scope="col">Ubicación</th>
            <th scope="col">Visibilidad</th>
            <th scope="col">Fecha</th>
            <th scope="col"><span class="sr-only">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($storedFiles as $storedFile)
            <tr>
              <td data-label="Archivo">
                @if($storedFile->visibility === 'private')
                  <a href="{{ route('director.resources.files.download', $storedFile->id) }}">{{ $storedFile->name }}</a>
                @else
                  <a href="{{ route('public.files.show', $storedFile->id) }}" target="_blank" rel="noopener noreferrer">{{ $storedFile->name }}</a>
                @endif
                <details class="director-resource-details" open>
                  <summary>Detalles del archivo</summary>
                  <dl>
                    <div><dt>Registrado por</dt><dd>{{ $storedFile->user?->name ?? 'Usuario eliminado' }}</dd></div>
                    <div><dt>Tipo</dt><dd>{{ $fileTypeOptions[$storedFile->file_type] ?? 'No especificado' }}</dd></div>
                    <div><dt>Formato</dt><dd>{{ $storedFile->mime ?: 'No especificado' }}</dd></div>
                    <div><dt>Tamaño</dt><dd>{{ $storedFile->size >= 1048576 ? number_format($storedFile->size / 1048576, 2).' MB' : number_format($storedFile->size / 1024, 1).' KB' }}</dd></div>
                    <div><dt>Grado</dt><dd>{{ $storedFile->grade ?: 'No especificado' }}</dd></div>
                    <div><dt>Estudiante relacionado</dt><dd>{{ $storedFile->student ? trim($storedFile->student->first_name.' '.$storedFile->student->last_name) : 'No asignado' }}</dd></div>
                    <div><dt>Descripción</dt><dd>{{ $storedFile->description ?: 'No se proporcionó descripción' }}</dd></div>
                    <div><dt>Disponibilidad</dt><dd>{{ $storedFile->available_from?->format('d/m/Y') ?? 'Sin inicio' }} — {{ $storedFile->available_until?->format('d/m/Y') ?? 'Sin límite' }}</dd></div>
                    <div><dt>Fecha de publicación</dt><dd>{{ $storedFile->published_at?->format('d/m/Y H:i') ?? 'No publicada' }}</dd></div>
                    <div><dt>Fecha de carga</dt><dd>{{ $storedFile->created_at->format('d/m/Y H:i') }}</dd></div>
                  </dl>
                </details>
              </td>
              <td data-label="Carpeta">{{ $sectionOptions[$storedFile->section] ?? $storedFile->section }}</td>
              <td data-label="Subcarpeta / grado">{{ \App\Support\StoredFileSections::subfolderFromPath($storedFile->section, $storedFile->path) ?? 'Sin subcarpeta' }}</td>
              <td data-label="Curso">{{ $storedFile->course?->name ?? '—' }}</td>
              <td data-label="Ubicación"><code>{{ $storedFile->disk === 'local' ? 'storage/app/private/' : 'storage/app/public/' }}{{ $storedFile->path }}</code></td>
              <td data-label="Visibilidad">{{ $storedFile->visibility === 'private' ? 'Privado' : 'Publicado' }}</td>
              <td data-label="Fecha">{{ $storedFile->created_at->format('d/m/Y H:i') }}</td>
              <td class="admin-actions-cell">
                <form method="POST" action="{{ route('director.resources.files.destroy', $storedFile->id) }}" class="confirm-delete">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger" type="submit" aria-label="Eliminar {{ $storedFile->name }}"><i class="fas fa-trash" aria-hidden="true"></i> Eliminar</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="muted-note">Todavía no hay archivos en estas carpetas.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $storedFiles->links() }}
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sectionSelect = document.getElementById('resourceSection');
      const subfolderGroup = document.getElementById('resourceSubfolderGroup');
      const subfolderSelect = document.getElementById('resourceSubfolder');
      const courseGroup = document.getElementById('resourceCourseGroup');
      const courseSelect = document.getElementById('resourceCourse');
      const weekGroup = document.getElementById('resourceWeekGroup');
      const weekInput = document.getElementById('resourceWeek');
      const subfoldersBySection = @json($subfolderOptions);
      const courseHierarchySections = new Set(@json($courseHierarchySections));
      const previousSubfolder = @json(old('subfolder'));
      const previousCourse = @json(old('course_id'));
      let selectedCourse = previousCourse;

      if (!sectionSelect || !subfolderGroup || !subfolderSelect || !courseGroup || !courseSelect || !weekGroup || !weekInput) return;

      const updateFolderFields = (selectedSubfolder = '') => {
        const currentCourse = courseSelect.value || selectedCourse;
        const subfolders = subfoldersBySection[sectionSelect.value] ?? {};
        subfolderSelect.replaceChildren(new Option('Selecciona un grado', ''));
        Object.entries(subfolders).forEach(([value, label]) => {
          subfolderSelect.add(new Option(label, value));
        });

        const hasSubfolders = Object.keys(subfolders).length > 0;
        subfolderGroup.hidden = !hasSubfolders;
        subfolderSelect.disabled = !hasSubfolders;
        subfolderSelect.required = hasSubfolders;
        if (hasSubfolders && Object.hasOwn(subfolders, selectedSubfolder)) {
          subfolderSelect.value = selectedSubfolder;
        }

        const requiresCourse = courseHierarchySections.has(sectionSelect.value) && subfolderSelect.value !== '';
        courseGroup.hidden = !requiresCourse;
        courseSelect.disabled = !requiresCourse;
        courseSelect.required = requiresCourse;
        if (requiresCourse && Array.from(courseSelect.options).some(option => option.value === String(currentCourse))) {
          courseSelect.value = currentCourse;
        } else if (!requiresCourse) {
          courseSelect.value = '';
        }
        selectedCourse = courseSelect.value;

        const needsWeek = sectionSelect.value === 'Recursos_Pedagogicos';
        weekGroup.hidden = !needsWeek;
        weekInput.disabled = !needsWeek;
        weekInput.required = needsWeek;
      };

      sectionSelect.addEventListener('change', () => updateFolderFields());
      subfolderSelect.addEventListener('change', () => updateFolderFields(subfolderSelect.value));
      updateFolderFields(previousSubfolder);
    });
  </script>
@endpush
