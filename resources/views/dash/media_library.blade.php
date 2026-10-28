@extends('layouts.admin')

@php $adminSection = 'media-library'; @endphp

@section('admin_content')
  <div class="admin-card media-library-page">
    <div class="section-header admin-page-header" style="text-align:left;">
      <h2 class="section-title">Biblioteca multimedia</h2>
      <div class="section-divider"></div>
      <p>Guarda archivos en una carpeta privada y ábrelos o reprodúcelos desde aquí. No se publican en el sitio.</p>
    </div>

    <section class="admin-panel media-library-upload" aria-labelledby="media-library-upload-title">
      <div>
        <h3 class="admin-panel-title" id="media-library-upload-title">Agregar archivo</h3>
        <p class="muted-note">Imágenes, videos, audios, PDF y otros documentos (máximo 500 MB por archivo).</p>
      </div>
      @if(session('success'))
        <div class="admin-alert-success" role="status">{{ session('success') }}</div>
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
      <form method="POST" action="{{ route('admin.media-library.store') }}" enctype="multipart/form-data" class="admin-form-stack">
        @csrf
        <label for="media-library-file">Selecciona un archivo</label>
        <input id="media-library-file" type="file" name="file" required />
        <div class="admin-actions-row">
          <button class="btn btn-primary" type="submit"><i class="fas fa-upload" aria-hidden="true"></i> Guardar en la carpeta</button>
        </div>
      </form>
    </section>

    <section class="media-library-content" aria-labelledby="media-library-files-title">
      <div class="media-library-heading">
        <div>
          <h3 class="admin-panel-title" id="media-library-files-title">Archivos guardados</h3>
          <p class="muted-note">{{ $files->total() }} {{ $files->total() === 1 ? 'archivo' : 'archivos' }} en esta carpeta privada.</p>
        </div>
      </div>

      @if($files->isEmpty())
        <div class="media-library-empty">
          <i class="fas fa-folder-open" aria-hidden="true"></i>
          <strong>La carpeta está vacía</strong>
          <span>Sube un archivo para verlo aquí.</span>
        </div>
      @else
        <div class="media-library-grid">
          @foreach($files as $file)
            @php
              $previewUrl = route('admin.media-library.preview', $file->id);
              $mime = $file->mime ?? '';
              $canPreviewImage = in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
              $sizeLabel = $file->size >= 1048576
                ? number_format($file->size / 1048576, 1).' MB'
                : number_format($file->size / 1024, 0).' KB';
            @endphp
            <article class="media-library-item">
              <div class="media-library-preview">
                @if($canPreviewImage)
                  <a href="{{ $previewUrl }}" target="_blank" rel="noopener" aria-label="Abrir imagen {{ $file->name }}">
                    <img src="{{ $previewUrl }}" alt="{{ $file->name }}" loading="lazy" />
                  </a>
                @elseif(str_starts_with($mime, 'video/'))
                  <video controls preload="metadata" aria-label="Reproducir {{ $file->name }}">
                    <source src="{{ $previewUrl }}" type="{{ $mime }}" />
                    Tu navegador no puede reproducir este video.
                  </video>
                @elseif(str_starts_with($mime, 'audio/'))
                  <div class="media-library-audio">
                    <i class="fas fa-headphones" aria-hidden="true"></i>
                    <audio controls preload="metadata" aria-label="Reproducir {{ $file->name }}">
                      <source src="{{ $previewUrl }}" type="{{ $mime }}" />
                      Tu navegador no puede reproducir este audio.
                    </audio>
                  </div>
                @elseif($mime === 'application/pdf')
                  <div class="media-library-file-icon media-library-pdf">
                    <i class="fas fa-file-pdf" aria-hidden="true"></i>
                    <span>PDF</span>
                  </div>
                @else
                  <div class="media-library-file-icon">
                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                    <span>Documento</span>
                  </div>
                @endif
              </div>
              <div class="media-library-item-details">
                <strong title="{{ $file->name }}">{{ $file->name }}</strong>
                <span>{{ $mime ?: 'Tipo de archivo no identificado' }} · {{ $sizeLabel }}</span>
                <span>Subido por {{ $file->user->name ?? 'Administrador' }} · {{ $file->created_at->format('d/m/Y') }}</span>
              </div>
              <div class="media-library-item-actions">
                <a class="btn btn-primary btn-sm" href="{{ $previewUrl }}" target="_blank" rel="noopener">
                  <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                  {{ $mime === 'application/pdf' || $canPreviewImage ? 'Abrir' : (str_starts_with($mime, 'video/') || str_starts_with($mime, 'audio/') ? 'Abrir reproductor' : 'Descargar') }}
                </a>
                <form method="POST" action="{{ route('admin.media-library.destroy', $file->id) }}" class="confirm-delete">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-outline-dark btn-sm" type="submit" aria-label="Eliminar {{ $file->name }}" title="Eliminar">
                    <i class="fas fa-trash" aria-hidden="true"></i>
                  </button>
                </form>
              </div>
            </article>
          @endforeach
        </div>
        <div class="admin-pagination">{{ $files->links() }}</div>
      @endif
    </section>
  </div>
@endsection
