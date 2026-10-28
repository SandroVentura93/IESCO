@extends('layouts.admin')

@php
  $adminSection = 'files-panel';
  $reopenFileId = old('modal_file_id', request()->query('edit'));
@endphp

@section('admin_content')
  <div class="admin-card files-panel files-panel-page">
    <header class="files-page-heading">
      <div>
        <span class="files-page-eyebrow">CENTRO DE CONTENIDO</span>
        <h1>Archivos y publicaciones</h1>
        <p>Organiza los recursos institucionales, administra su visibilidad y mantén el contenido al día.</p>
      </div>
      <a class="btn btn-primary files-upload-shortcut" href="#upload-file">
        <i class="fas fa-cloud-arrow-up" aria-hidden="true"></i>
        Subir archivo
      </a>
    </header>

    @if(session('success'))
      <div class="admin-alert-success files-feedback" role="status">
        <i class="fas fa-circle-check" aria-hidden="true"></i>{{ session('success') }}
      </div>
    @endif
    @if($errors->any())
      <div class="alert alert-error files-feedback" role="alert">
        <strong>No se pudo completar la acción.</strong>
        <ul>
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <section class="files-overview" aria-label="Resumen de archivos">
      <article class="files-overview-card">
        <span class="files-overview-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></span>
        <div><span>Total de archivos</span><strong>{{ number_format($fileStats['total']) }}</strong></div>
      </article>
      <article class="files-overview-card">
        <span class="files-overview-icon files-overview-published"><i class="fas fa-globe" aria-hidden="true"></i></span>
        <div><span>Publicados</span><strong>{{ number_format($fileStats['published']) }}</strong></div>
      </article>
      <article class="files-overview-card">
        <span class="files-overview-icon files-overview-private"><i class="fas fa-lock" aria-hidden="true"></i></span>
        <div><span>Privados</span><strong>{{ number_format($fileStats['private']) }}</strong></div>
      </article>
    </section>

    <div class="admin-split-grid files-workspace">
      <div class="admin-panel files-upload-panel">
        <div class="files-panel-heading">
          <span class="files-panel-heading-icon"><i class="fas fa-cloud-arrow-up" aria-hidden="true"></i></span>
          <div>
            <h2 class="admin-panel-title">Subir nuevo archivo</h2>
            <p>Completa los detalles para guardar y compartir un recurso.</p>
          </div>
        </div>
        <form method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data" class="admin-form-stack">
          @csrf
          <label for="upload-file">Archivo</label>
          <input id="upload-file" type="file" name="file" required accept="image/*,video/*,audio/*,application/*" />
          <label for="upload-section">Guardar en carpeta</label>
          <select id="upload-section" name="section" required>
            <option value="" disabled {{ old('section') ? '' : 'selected' }}>Selecciona la sección de destino</option>
            @foreach($projectDestinationOptions as $value => $label)
              <option value="{{ $value }}" {{ old('section') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <div id="upload-subfolder-group" hidden>
            <label for="upload-subfolder">Grado</label>
            <select id="upload-subfolder" name="subfolder" disabled>
              <option value="">Selecciona un grado</option>
            </select>
          </div>
          <div id="upload-course-group" hidden>
            <label for="upload-course">Curso</label>
            <select id="upload-course" name="course_id" disabled>
              <option value="">Selecciona un curso</option>
              @foreach($courses as $course)
                <option value="{{ $course['id'] }}" {{ (string) old('course_id') === (string) $course['id'] ? 'selected' : '' }}>{{ $course['name'] }}</option>
              @endforeach
            </select>
          </div>
          <div id="upload-student-group" hidden>
            <label for="upload-student">Alumno (opcional)</label>
            <select id="upload-student" name="student_id" disabled>
              <option value="">Sin alumno asignado</option>
              @foreach($students as $student)
                <option value="{{ $student->id }}" {{ (string) old('student_id') === (string) $student->id ? 'selected' : '' }}>
                  {{ $student->last_name }}, {{ $student->first_name }}{{ $student->grade ? ' · '.$student->grade : '' }}
                </option>
              @endforeach
            </select>
            @if($students->isEmpty())
              <span class="admin-form-help">No hay alumnos registrados; puedes continuar sin asignar uno.</span>
            @endif
          </div>
          <p class="admin-form-help files-upload-note"><i class="fas fa-circle-info" aria-hidden="true"></i> El archivo se guardará en la carpeta de Proyecto IJENT que selecciones.</p>
          <label for="upload-visibility">Visibilidad</label>
          <select id="upload-visibility" name="visibility" required>
            <option value="published" {{ old('visibility', 'published') === 'published' ? 'selected' : '' }}>Publicado (visible en el sitio)</option>
            <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Privado (solo administración)</option>
          </select>
          <fieldset class="publication-window">
            <legend>Programar disponibilidad (opcional)</legend>
            <p>El contenido se mostrará desde la fecha inicial hasta la fecha final, ambas inclusive. Deja los campos vacíos para mostrarlo sin límite de fechas.</p>
            <div class="publication-window-fields">
              <label for="available-from">Mostrar desde
                <input id="available-from" type="date" name="available_from" value="{{ old('available_from') }}" />
              </label>
              <label for="available-until">Mostrar hasta
                <input id="available-until" type="date" name="available_until" value="{{ old('available_until') }}" />
              </label>
            </div>
          </fieldset>
          <label for="upload-grade">Grado (opcional)</label>
          <select id="upload-grade" name="grade">
            <option value="">(no especificado)</option>
            <option {{ old('grade') === '1°' ? 'selected' : '' }}>1°</option>
            <option {{ old('grade') === '2°' ? 'selected' : '' }}>2°</option>
            <option {{ old('grade') === '3°' ? 'selected' : '' }}>3°</option>
            <option {{ old('grade') === '4°' ? 'selected' : '' }}>4°</option>
            <option {{ old('grade') === '5°' ? 'selected' : '' }}>5°</option>
            <option {{ old('grade') === '6°' ? 'selected' : '' }}>6°</option>
            <option {{ old('grade') === 'Otro' ? 'selected' : '' }}>Otro</option>
          </select>
          <label for="upload-file-type">Tipo de archivo</label>
          <select id="upload-file-type" name="file_type" required>
            <option value="auto" {{ old('file_type', 'auto') === 'auto' ? 'selected' : '' }}>Detectar automáticamente</option>
            <option value="image">Imagen</option>
            <option value="document">Documento</option>
            <option value="video">Video</option>
            <option value="audio">Audio</option>
            <option value="other">Otro</option>
          </select>
          <label for="upload-description">Descripción (opcional)</label>
          <textarea id="upload-description" name="description" rows="3" placeholder="Breve descripción que aparecerá junto al archivo...">{{ old('description') }}</textarea>
          <div class="admin-actions-row">
            <button class="btn btn-primary" type="submit">Subir archivo</button>
          </div>
        </form>
      </div>

      <div class="admin-panel admin-panel-wide files-panel">
        <div class="files-panel-header">
          <div class="files-list-heading">
            <h2 class="admin-panel-title">Biblioteca de archivos</h2>
            <p>{{ $files->total() }} {{ $files->total() === 1 ? 'recurso encontrado' : 'recursos encontrados' }}</p>
          </div>
          <div class="files-panel-toolbar">
            <form method="GET" action="{{ route('admin.files.panel') }}" class="files-filter-form">
              <label class="files-search-field">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o descripción..." aria-label="Buscar archivos" />
              </label>
              <select name="visibility" class="form-control" aria-label="Filtrar por visibilidad">
                <option value="">Toda visibilidad</option>
                <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Privados</option>
                <option value="published" {{ request('visibility') === 'published' ? 'selected' : '' }}>Publicados</option>
              </select>
              <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Filtrar</button>
              @if(request()->filled('q') || request()->filled('visibility'))
                <a class="btn btn-outline-dark btn-sm" href="{{ route('admin.files.panel') }}">Limpiar</a>
              @endif
            </form>
            <div class="files-view-toggle" role="group" aria-label="Modo de visualización">
              <button class="btn btn-sm btn-outline-dark" id="viewTableBtn" type="button" title="Vista tabla" aria-label="Vista tabla"><i class="fas fa-table"></i></button>
              <button class="btn btn-sm btn-outline-dark" id="viewGridBtn" type="button" title="Vista tarjetas" aria-label="Vista tarjetas"><i class="fas fa-th-large"></i></button>
            </div>
          </div>
        </div>

        <div class="admin-table-wrap files-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th style="width:140px;">Preview</th>
                <th>Nombre / Descripción</th>
                <th>Sección</th>
                <th>Disponible</th>
                <th>Grado</th>
                <th>Alumno</th>
                <th>Propietario</th>
                <th>Tipo</th>
                <th>Visibilidad</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($files as $f)
                @php $url = route('admin.files.preview', $f->id); @endphp
                <tr class="file-row">
                  <td style="width:140px;">
                    <div class="file-thumb" style="width:120px;height:80px;border-radius:8px;overflow:hidden;background:#f3f6fb;display:grid;place-items:center;border:1px solid #eef4fb;">
                      @if($f->file_type === 'image')
                        <img src="{{ $url }}" alt="{{ $f->name }}" style="width:100%;height:100%;object-fit:cover;" />
                      @elseif($f->file_type === 'video')
                        <video src="{{ $url }}" style="width:100%;height:100%;object-fit:cover;" muted preload="metadata"></video>
                      @elseif($f->file_type === 'audio')
                        <i class="fas fa-file-audio fa-2x" style="color:#64748b;"></i>
                      @else
                        <i class="fas fa-file fa-2x" style="color:#64748b;"></i>
                      @endif
                    </div>
                  </td>
                  <td>
                    <div style="display:flex;flex-direction:column;gap:.2rem;">
                      <strong class="file-name">{{ $f->name }}</strong>
                      <span class="muted" style="font-size:.9rem;max-width:420px;">{{ Str::limit($f->description, 140) }}</span>
                    </div>
                  </td>
                  <td>{{ $f->section ? ($sectionOptions[$f->section] ?? $f->section) : '—' }}</td>
                  <td>
                    @if($f->available_from || $f->available_until)
                      <span>{{ $f->available_from?->format('d/m/Y') ?? 'Sin inicio' }}</span>
                      <span aria-hidden="true"> — </span>
                      <span>{{ $f->available_until?->format('d/m/Y') ?? 'Sin límite' }}</span>
                    @else
                      <span>Sin límite</span>
                    @endif
                  </td>
                  <td>{{ $f->grade ?? '—' }}</td>
                  <td>{{ $f->student ? $f->student->last_name.', '.$f->student->first_name : '—' }}</td>
                  <td>{{ $f->user->name ?? '—' }}</td>
                  <td>{{ $f->file_type ?? ($f->mime ? explode('/', $f->mime)[0] : '—') }}</td>
                  <td>{{ $f->visibility }}</td>
                  <td class="admin-actions-cell">
                    <div class="files-row-actions">
                      @include('dash.partials.file_edit_trigger', ['file' => $f])
                      <a href="{{ route('admin.files.download', $f->id) }}" class="btn btn-outline-dark btn-sm" title="Descargar" aria-label="Descargar {{ $f->name }}"><i class="fas fa-download"></i></a>
                      <form method="POST" action="{{ route('admin.files.update_section', $f->id) }}" class="admin-inline-form" style="display:inline-flex;gap:.25rem;align-items:center;">
                        @csrf
                        <select name="section" style="padding:.25rem;border-radius:6px;border:1px solid #e6eef8;font-size:.85rem;">
                          <option value="">(no asignada)</option>
                          @foreach($sectionOptions as $value => $label)
                            <option value="{{ $value }}" {{ $f->section === $value ? 'selected' : '' }}>{{ $label }}</option>
                          @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-dark" type="submit" title="Mover"><i class="fas fa-arrow-right"></i></button>
                      </form>
                      @if($f->visibility !== 'published')
                        <form method="POST" action="{{ route('admin.files.publish', $f->id) }}" class="admin-inline-form">@csrf<button class="btn btn-primary btn-sm" type="submit" title="Publicar"><i class="fas fa-globe"></i></button></form>
                      @else
                        <form method="POST" action="{{ route('admin.files.unpublish', $f->id) }}" class="admin-inline-form">@csrf<button class="btn btn-outline-dark btn-sm" type="submit" title="Despublicar"><i class="fas fa-eye-slash"></i></button></form>
                      @endif
                      <form method="POST" action="{{ route('admin.files.destroy', $f->id) }}" class="confirm-delete admin-inline-form">@csrf @method('DELETE')<button class="btn btn-outline-dark btn-sm" type="submit" title="Eliminar"><i class="fas fa-trash"></i></button></form>
                    </div>
                  </td>
                </tr>
              @endforeach
              @if($files->isEmpty())
                <tr><td class="files-empty-row" colspan="10"><i class="fas fa-folder-open" aria-hidden="true"></i><strong>No se encontraron archivos</strong><span>Prueba otra búsqueda o sube un nuevo recurso institucional.</span></td></tr>
              @endif
            </tbody>
          </table>
        </div>
        <!-- Grid view (hidden by default) -->
        <div class="files-grid" style="display:none;margin-top:1rem;">
          <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;">
            @foreach($files as $f)
              @php $url = route('admin.files.preview', $f->id); @endphp
              <div class="file-card admin-card files-library-card">
                <div class="file-thumb" style="height:160px;border-radius:8px;overflow:hidden;display:grid;place-items:center;background:#f8fbff;border:1px solid #eef4fb;">
                  @if($f->file_type === 'image')
                    <img src="{{ $url }}" alt="{{ $f->name }}" style="width:100%;height:100%;object-fit:cover;" />
                  @elseif($f->file_type === 'video')
                    <video src="{{ $url }}" style="width:100%;height:100%;object-fit:cover;" muted preload="metadata"></video>
                  @elseif($f->file_type === 'audio')
                    <i class="fas fa-file-audio fa-2x" style="color:#64748b;"></i>
                  @else
                    <i class="fas fa-file fa-2x" style="color:#64748b;"></i>
                  @endif
                </div>
                <div class="files-card-info">
                  <strong>{{ $f->name }}</strong>
                  <span class="muted" style="font-size:.9rem;">{{ Str::limit($f->description, 140) }}</span>
                  <span class="muted" style="font-size:.78rem;">
                    {{ $f->section ?? 'Sin sección' }} ·
                    @if($f->available_from || $f->available_until)
                      {{ $f->available_from?->format('d/m/Y') ?? 'Sin inicio' }} — {{ $f->available_until?->format('d/m/Y') ?? 'Sin límite' }}
                    @else
                      Sin límite de fechas
                    @endif
                  </span>
                </div>
                <div class="files-card-actions">
                  @include('dash.partials.file_edit_trigger', ['file' => $f])
                  <a href="{{ route('admin.files.download', $f->id) }}" class="btn btn-outline-dark btn-sm" title="Descargar"><i class="fas fa-download"></i></a>
                  @if($f->visibility !== 'published')
                    <form method="POST" action="{{ route('admin.files.publish', $f->id) }}" class="admin-inline-form">@csrf<button class="btn btn-primary btn-sm" type="submit" title="Publicar"><i class="fas fa-globe"></i></button></form>
                  @else
                    <form method="POST" action="{{ route('admin.files.unpublish', $f->id) }}" class="admin-inline-form">@csrf<button class="btn btn-outline-dark btn-sm" type="submit" title="Despublicar"><i class="fas fa-eye-slash"></i></button></form>
                  @endif
                  <form method="POST" action="{{ route('admin.files.destroy', $f->id) }}" class="confirm-delete admin-inline-form">@csrf @method('DELETE')<button class="btn btn-outline-dark btn-sm" type="submit" title="Eliminar"><i class="fas fa-trash"></i></button></form>
                </div>
              </div>
            @endforeach
            @if($files->isEmpty())
              <div class="files-grid-empty"><i class="fas fa-folder-open" aria-hidden="true"></i><strong>No se encontraron archivos</strong><span>Prueba otra búsqueda o sube un nuevo recurso institucional.</span></div>
            @endif
          </div>
        </div>
        <div class="admin-pagination">{{ $files->links() }}</div>
      </div>
    </div>
    @include('dash.partials.file_edit_modal')
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toggleView = (view) => {
        const panel = document.querySelector('.files-panel-page');
        if (!panel) return;
        panel.classList.toggle('view-grid', view === 'grid');
        document.getElementById('viewTableBtn')?.classList.toggle('active', view === 'table');
        document.getElementById('viewGridBtn')?.classList.toggle('active', view === 'grid');
        try {
          localStorage.setItem('files-panel-view', view);
        } catch (error) {
          console.warn('No se pudo guardar la preferencia de vista de archivos.', error);
        }
      };
      document.getElementById('viewTableBtn')?.addEventListener('click', () => toggleView('table'));
      document.getElementById('viewGridBtn')?.addEventListener('click', () => toggleView('grid'));
      let savedView = 'table';
      try {
        savedView = localStorage.getItem('files-panel-view') === 'grid' ? 'grid' : 'table';
      } catch (error) {
        console.warn('No se pudo cargar la preferencia de vista de archivos.', error);
      }
      toggleView(savedView);

      const editModal = document.getElementById('fileEditModal');
      const editForm = document.getElementById('fileEditModalForm');
      const deleteForm = document.getElementById('fileEditDeleteForm');
      const editTitle = document.getElementById('fileEditModalName');
      const currentFileLink = document.getElementById('fileEditCurrentFile');
      const sectionOptions = @json($subfolderOptions);
      const modalCourseHierarchySections = new Set(@json($courseHierarchySections));
      const oldValues = @json(old());
      const reopenFileId = @json($reopenFileId);
      let editTrigger = null;

      const updateEditLocationFields = (subfolderValue = '', courseValue = '', studentValue = document.getElementById('modal-edit-student').value) => {
        const sectionSelect = document.getElementById('modal-edit-section');
        const subfolderGroup = document.getElementById('modal-edit-subfolder-group');
        const subfolderSelect = document.getElementById('modal-edit-subfolder');
        const courseGroup = document.getElementById('modal-edit-course-group');
        const courseSelect = document.getElementById('modal-edit-course');
        const studentGroup = document.getElementById('modal-edit-student-group');
        const studentSelect = document.getElementById('modal-edit-student');
        const subfolders = sectionOptions[sectionSelect.value] ?? {};

        subfolderSelect.replaceChildren(new Option('Selecciona un grado', ''));
        Object.entries(subfolders).forEach(([value, label]) => {
          subfolderSelect.add(new Option(label, value));
        });
        subfolderGroup.hidden = Object.keys(subfolders).length === 0;
        subfolderSelect.disabled = subfolderGroup.hidden;
        if (Object.hasOwn(subfolders, subfolderValue)) {
          subfolderSelect.value = subfolderValue;
        }

        const requiresCourse = modalCourseHierarchySections.has(sectionSelect.value)
          && subfolderSelect.value !== '';
        courseGroup.hidden = !requiresCourse;
        courseSelect.disabled = !requiresCourse;
        courseSelect.required = requiresCourse;
        courseSelect.value = requiresCourse && Array.from(courseSelect.options).some(option => option.value === String(courseValue))
          ? String(courseValue)
          : '';

        const isActivitiesSection = sectionSelect.value === 'Actividades_Extracurriculares';
        studentGroup.hidden = !isActivitiesSection;
        studentSelect.disabled = !isActivitiesSection;
        studentSelect.value = isActivitiesSection ? String(studentValue) : '';
      };

      const openEditModal = trigger => {
        if (!editModal || !editForm) return;
        editTrigger = trigger;
        editForm.reset();
        editForm.action = trigger.dataset.updateUrl;
        deleteForm.action = trigger.dataset.deleteUrl;

        const values = trigger.dataset.fileId === String(reopenFileId) ? oldValues : {};
        const value = (field, fallback) => values[field] ?? fallback ?? '';
        document.getElementById('modal-file-id').value = trigger.dataset.fileId || '';
        editTitle.textContent = trigger.dataset.fileName || 'Editar archivo';
        document.getElementById('modal-edit-name').value = value('name', trigger.dataset.fileName);
        document.getElementById('modal-edit-description').value = value('description', trigger.dataset.fileDescription);
        document.getElementById('modal-edit-section').value = value('section', trigger.dataset.fileSection);
        document.getElementById('modal-edit-grade').value = value('grade', trigger.dataset.fileGrade);
        document.getElementById('modal-edit-file-type').value = value('file_type', trigger.dataset.fileType);
        document.getElementById('modal-edit-visibility').value = value('visibility', trigger.dataset.fileVisibility);
        document.getElementById('modal-edit-available-from').value = value('available_from', trigger.dataset.fileAvailableFrom);
        document.getElementById('modal-edit-available-until').value = value('available_until', trigger.dataset.fileAvailableUntil);
        currentFileLink.href = trigger.dataset.filePreview || '#';

        updateEditLocationFields(
          value('subfolder', trigger.dataset.fileSubfolder),
          value('course_id', trigger.dataset.fileCourse),
          value('student_id', trigger.dataset.fileStudent)
        );
        editModal.showModal();
        document.getElementById('modal-edit-name').focus();
      };

      document.querySelectorAll('[data-file-edit]').forEach(trigger => {
        trigger.addEventListener('click', event => {
          event.preventDefault();
          openEditModal(trigger);
        });
      });

      document.querySelectorAll('[data-close-file-edit]').forEach(button => {
        button.addEventListener('click', () => editModal?.close());
      });
      editModal?.addEventListener('click', event => {
        if (event.target === editModal) editModal.close();
      });
      editModal?.addEventListener('close', () => editTrigger?.focus());

      document.getElementById('modal-edit-section')?.addEventListener('change', () => {
        updateEditLocationFields('', '', document.getElementById('modal-edit-student').value);
      });
      document.getElementById('modal-edit-subfolder')?.addEventListener('change', event => {
        updateEditLocationFields(
          event.currentTarget.value,
          document.getElementById('modal-edit-course').value,
          document.getElementById('modal-edit-student').value
        );
      });

      if (reopenFileId) {
        const trigger = Array.from(document.querySelectorAll('[data-file-edit]'))
          .find(button => button.dataset.fileId === String(reopenFileId));
        if (trigger) openEditModal(trigger);
      }

      const sectionSelect = document.getElementById('upload-section');
      const subfolderGroup = document.getElementById('upload-subfolder-group');
      const subfolderSelect = document.getElementById('upload-subfolder');
      const courseGroup = document.getElementById('upload-course-group');
      const courseSelect = document.getElementById('upload-course');
      const studentGroup = document.getElementById('upload-student-group');
      const studentSelect = document.getElementById('upload-student');
      const subfoldersBySection = @json($subfolderOptions);
      const courseHierarchySections = new Set(@json($courseHierarchySections));
      const previousSubfolder = @json(old('subfolder'));
      const previousCourse = @json(old('course_id'));

      if (!sectionSelect || !subfolderGroup || !subfolderSelect || !courseGroup || !courseSelect || !studentGroup || !studentSelect) return;

      const courseExists = id => Array.from(courseSelect.options).some(option => option.value === String(id));
      let selectedCourse = previousCourse;
      const updateSubfolders = (selectedSubfolder = '') => {
        const currentCourse = courseSelect.value || selectedCourse;
        const subfolders = subfoldersBySection[sectionSelect.value] ?? {};
        subfolderSelect.replaceChildren(new Option('Selecciona un grado', ''));

        Object.entries(subfolders).forEach(([value, label]) => {
          subfolderSelect.add(new Option(label, value));
        });

        const hasSubfolders = Object.keys(subfolders).length > 0;
        subfolderGroup.hidden = !hasSubfolders;
        subfolderSelect.disabled = !hasSubfolders;
        subfolderSelect.required = courseHierarchySections.has(sectionSelect.value) && hasSubfolders;

        if (hasSubfolders && Object.hasOwn(subfolders, selectedSubfolder)) {
          subfolderSelect.value = selectedSubfolder;
        }

        const isActivitiesSection = sectionSelect.value === 'Actividades_Extracurriculares';
        studentGroup.hidden = !isActivitiesSection;
        studentSelect.disabled = !isActivitiesSection;

        const requiresCourse = courseHierarchySections.has(sectionSelect.value)
          && hasSubfolders
          && subfolderSelect.value !== '';
        courseGroup.hidden = !requiresCourse;
        courseSelect.disabled = !requiresCourse;
        courseSelect.required = requiresCourse;
        if (requiresCourse && courseExists(currentCourse)) {
          courseSelect.value = currentCourse;
        } else if (!requiresCourse) {
          courseSelect.value = '';
        }
        selectedCourse = courseSelect.value;
      };

      sectionSelect.addEventListener('change', () => updateSubfolders());
      subfolderSelect.addEventListener('change', () => updateSubfolders(subfolderSelect.value));
      updateSubfolders(previousSubfolder);
    });
  </script>
@endpush