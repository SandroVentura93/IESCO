<dialog class="file-edit-modal" id="fileEditModal" aria-labelledby="fileEditModalTitle">
  <div class="file-edit-modal-content">
    <header class="file-edit-modal-header">
      <div>
        <span class="file-edit-eyebrow">GESTIÓN DE CONTENIDO</span>
        <h2 id="fileEditModalTitle">Editar archivo</h2>
        <p id="fileEditModalName"></p>
      </div>
      <button class="file-edit-modal-close" type="button" data-close-file-edit aria-label="Cerrar">&times;</button>
    </header>

    @if($errors->any())
      <div class="alert alert-error file-edit-modal-errors" role="alert">
        <strong>No se pudieron guardar los cambios.</strong>
        <ul>
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="" class="confirm-delete" id="fileEditDeleteForm">
      @csrf
      @method('DELETE')
    </form>

    <form method="POST" action="" enctype="multipart/form-data" class="file-edit-modal-form" id="fileEditModalForm">
      @csrf
      @method('PUT')
      <input type="hidden" name="modal_file_id" id="modal-file-id" />

      <section class="file-edit-modal-section" aria-labelledby="fileEditInformationTitle">
        <h3 id="fileEditInformationTitle">Información del archivo</h3>
        <label class="file-edit-modal-field file-edit-modal-field-full" for="modal-edit-name">
          <span>Nombre del archivo</span>
          <input id="modal-edit-name" name="name" type="text" maxlength="255" required />
        </label>
        <label class="file-edit-modal-field file-edit-modal-field-full" for="modal-edit-description">
          <span>Descripción <small>Opcional</small></span>
          <textarea id="modal-edit-description" name="description" rows="3" maxlength="2000" placeholder="Añade una descripción útil para este recurso..."></textarea>
        </label>
        <div class="file-edit-modal-current">
          <span>Archivo actual</span>
          <a id="fileEditCurrentFile" href="#" target="_blank" rel="noopener">Abrir archivo</a>
        </div>
        <label class="file-edit-modal-field file-edit-modal-field-full" for="modal-edit-file">
          <span>Reemplazar archivo <small>Opcional</small></span>
          <input id="modal-edit-file" name="file" type="file" accept="image/*,video/*,audio/*,application/*" />
          <small>Déjalo vacío para conservarlo. Tamaño máximo: 500 MB.</small>
        </label>
      </section>

      <section class="file-edit-modal-section" aria-labelledby="fileEditOrganizationTitle">
        <h3 id="fileEditOrganizationTitle">Organización y acceso</h3>
        <div class="file-edit-modal-fields">
          <label class="file-edit-modal-field" for="modal-edit-section">
            <span>Sección de destino</span>
            <select id="modal-edit-section" name="section">
              <option value="">Sin sección</option>
              @foreach($sectionOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </label>
          <div class="file-edit-modal-field" id="modal-edit-subfolder-group" hidden>
            <label for="modal-edit-subfolder">Carpeta de grado</label>
            <select id="modal-edit-subfolder" name="subfolder" disabled>
              <option value="">Selecciona un grado</option>
            </select>
          </div>
          <div class="file-edit-modal-field" id="modal-edit-course-group" hidden>
            <label for="modal-edit-course">Curso</label>
            <select id="modal-edit-course" name="course_id" disabled>
              <option value="">Selecciona un curso</option>
              @foreach($courses as $course)
                <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="file-edit-modal-field" id="modal-edit-student-group" hidden>
            <label for="modal-edit-student">Alumno <small>Opcional</small></label>
            <select id="modal-edit-student" name="student_id" disabled>
              <option value="">Sin alumno asignado</option>
              @foreach($students as $student)
                <option value="{{ $student->id }}">{{ $student->last_name }}, {{ $student->first_name }}{{ $student->grade ? ' · '.$student->grade : '' }}</option>
              @endforeach
            </select>
          </div>
          <label class="file-edit-modal-field" for="modal-edit-grade">
            <span>Grado académico <small>Opcional</small></span>
            <select id="modal-edit-grade" name="grade">
              <option value="">No especificado</option>
              @foreach(['1°', '2°', '3°', '4°', '5°', '6°', 'Otro'] as $grade)
                <option value="{{ $grade }}">{{ $grade }}</option>
              @endforeach
            </select>
          </label>
          <label class="file-edit-modal-field" for="modal-edit-file-type">
            <span>Tipo de archivo</span>
            <select id="modal-edit-file-type" name="file_type" required>
              @foreach(['image' => 'Imagen', 'document' => 'Documento', 'video' => 'Video', 'audio' => 'Audio', 'other' => 'Otro'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </label>
          <label class="file-edit-modal-field" for="modal-edit-visibility">
            <span>Visibilidad</span>
            <select id="modal-edit-visibility" name="visibility" required>
              <option value="published">Publicado · visible en el sitio</option>
              <option value="private">Privado · solo administración</option>
            </select>
          </label>
        </div>
      </section>

      <section class="file-edit-modal-section" aria-labelledby="fileEditAvailabilityTitle">
        <h3 id="fileEditAvailabilityTitle">Disponibilidad</h3>
        <p>Deja las fechas vacías para que el archivo no tenga límite de disponibilidad.</p>
        <div class="file-edit-modal-fields">
          <label class="file-edit-modal-field" for="modal-edit-available-from">
            <span>Mostrar desde</span>
            <input id="modal-edit-available-from" type="date" name="available_from" />
          </label>
          <label class="file-edit-modal-field" for="modal-edit-available-until">
            <span>Mostrar hasta</span>
            <input id="modal-edit-available-until" type="date" name="available_until" />
          </label>
        </div>
      </section>

      <footer class="file-edit-modal-actions">
        <button class="btn file-edit-modal-delete" type="submit" form="fileEditDeleteForm">
          <i class="fas fa-trash" aria-hidden="true"></i>
          Eliminar archivo
        </button>
        <div class="file-edit-modal-actions-end">
          <button class="btn btn-outline-dark" type="button" data-close-file-edit>Cancelar</button>
          <button class="btn btn-primary" type="submit">
            <i class="fas fa-check" aria-hidden="true"></i>
            Guardar cambios
          </button>
        </div>
      </footer>
    </form>
  </div>
</dialog>
