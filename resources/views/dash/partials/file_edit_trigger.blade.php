<a
  href="{{ route('admin.files.edit', $file->id) }}"
  class="btn btn-outline-dark btn-sm"
  data-file-edit
  data-file-id="{{ $file->id }}"
  data-update-url="{{ route('admin.files.update', $file->id) }}"
  data-delete-url="{{ route('admin.files.destroy', $file->id) }}"
  data-file-preview="{{ route('admin.files.preview', $file->id) }}"
  data-file-name="{{ $file->name }}"
  data-file-description="{{ $file->description }}"
  data-file-section="{{ $file->section }}"
  data-file-subfolder="{{ $file->section ? \App\Support\StoredFileSections::subfolderFromPath($file->section, $file->path) : '' }}"
  data-file-course="{{ $file->course_id }}"
  data-file-student="{{ $file->student_id }}"
  data-file-grade="{{ $file->grade }}"
  data-file-type="{{ $file->file_type ?: 'other' }}"
  data-file-visibility="{{ $file->visibility }}"
  data-file-available-from="{{ $file->available_from?->format('Y-m-d') }}"
  data-file-available-until="{{ $file->available_until?->format('Y-m-d') }}"
  aria-label="Editar detalles de {{ $file->name }}"
>
  <i class="fas fa-pen" aria-hidden="true"></i><span>Editar</span>
</a>
