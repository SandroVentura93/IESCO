@php
  $editing = isset($page);
  $homepageMode = $homepageMode ?? false;
  $currentSection = old('section', $page->section ?? ($homepageMode ? 'inicio' : 'institucional'));
  $currentSlug = old('slug', $page->slug ?? ($homepageMode ? 'inicio' : ''));
  $isHomepage = $currentSlug === 'inicio';
  $homeDefaults = [
    'inicio_badge' => 'Tumbadén, San Pablo — Cajamarca, Perú',
    'inicio_title_prefix' => 'IE.:',
    'inicio_title_highlight' => 'S.C.O.',
    'inicio_institution_name' => 'Salomón Cáceres Oliva',
    'inicio_motto' => config('school.motto'),
    'inicio_subtitle' => 'Formamos ciudadanos íntegros con valores, conocimiento y habilidades para el Perú del siglo XXI.',
    'inicio_scroll_text' => 'Explorar',
    'inicio_left_button_text' => 'Ver publicaciones',
    'inicio_left_button_url' => '#publicaciones',
    'inicio_right_button_text' => 'Conócenos',
    'inicio_right_button_url' => '#quienes-somos',
    'inicio_students_value' => config('school.stats.students'),
    'inicio_students_label' => 'Estudiantes',
    'inicio_teachers_value' => config('school.stats.teachers'),
    'inicio_teachers_label' => 'Docentes',
    'inicio_years_value' => config('school.stats.years'),
    'inicio_years_label' => 'Años de historia',
    'inicio_projects_value' => config('school.stats.projects'),
    'inicio_projects_label' => 'Proyectos activos',
  ];
  $homeValue = fn ($key) => old($key, $homeSettings[$key] ?? $homeDefaults[$key] ?? '');
  $currentVideo = $homeSettings['video'] ?? '';
  $currentVideoUrl = $homeSettings['video_url'] ?? '';
@endphp

<div class="admin-card page-editor">
  <header class="page-editor-header">
    <div>
      <span class="page-manager-eyebrow">{{ $editing ? 'Edición de contenido' : 'Nuevo contenido' }}</span>
      <h2 class="section-title">{{ $editing ? 'Editar página' : 'Crear página' }}</h2>
      <p>Completa el contenido, asígnalo a una sección y publica cuando esté listo.</p>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-dark"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al listado</a>
  </header>

  @if(session('success'))
    <div class="admin-alert-success" role="status">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-error page-editor-errors" role="alert">
      <strong>Revisa estos campos antes de guardar:</strong>
      <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST"
        action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}"
        class="admin-form-stack page-editor-form"
        enctype="multipart/form-data">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="page-editor-grid">
      <label class="page-editor-field">
        Título de la página
        <input type="text" name="title" id="page-title" value="{{ old('title', $page->title ?? ($homepageMode ? 'Inicio' : '')) }}" maxlength="255" required />
      </label>

      <label class="page-editor-field">
        Sección del sitio
        <select name="section" required>
          @foreach($sections as $key => $label)
            <option value="{{ $key }}" {{ $currentSection === $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </label>

      <label class="page-editor-field page-editor-slug">
        Dirección (slug)
        <span class="page-slug-input">
          <span>{{ url('/paginas') }}/</span>
          <input type="text" name="slug" id="page-slug" value="{{ $currentSlug }}" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" maxlength="255" required />
        </span>
        <small>Se genera desde el título. Usa <code>inicio</code> o <code>quienes-somos</code> para editar esas áreas principales de la portada.</small>
      </label>

      <label class="page-editor-field">
        Posición
        <input type="number" name="position" min="0" max="9999" value="{{ old('position', $page->position ?? 0) }}" />
        <small>Las páginas con posición menor se muestran primero.</small>
      </label>
    </div>

    <fieldset class="homepage-editor-fields" id="homepage-fields" {{ $isHomepage ? '' : 'hidden' }}>
      <legend>Diseño de la portada · sección Inicio</legend>
      <p class="page-editor-help">Edita los textos, accesos y recursos que aparecen en la portada. Los cambios se reflejan al guardar.</p>

      <div class="page-editor-grid">
        <label class="page-editor-field">
          Texto sobre el título
          <input type="text" name="inicio_badge" value="{{ $homeValue('inicio_badge') }}" maxlength="120" />
        </label>
        <label class="page-editor-field">
          Prefijo del título
          <input type="text" name="inicio_title_prefix" value="{{ $homeValue('inicio_title_prefix') }}" maxlength="100" />
        </label>
        <label class="page-editor-field">
          Texto destacado del título
          <input type="text" name="inicio_title_highlight" value="{{ $homeValue('inicio_title_highlight') }}" maxlength="150" />
        </label>
        <label class="page-editor-field">
          Nombre de la institución
          <input type="text" name="inicio_institution_name" value="{{ $homeValue('inicio_institution_name') }}" maxlength="200" />
        </label>
        <label class="page-editor-field page-editor-slug">
          Lema institucional
          <input type="text" name="inicio_motto" value="{{ $homeValue('inicio_motto') }}" maxlength="200" />
        </label>
        <label class="page-editor-field page-editor-slug">
          Texto de bienvenida
          <textarea name="inicio_subtitle" rows="3" maxlength="500">{{ $homeValue('inicio_subtitle') }}</textarea>
          <small>Se permiten saltos de línea; el contenido se muestra como texto seguro.</small>
        </label>
        <label class="page-editor-field">
          Texto del indicador para bajar
          <input type="text" name="inicio_scroll_text" value="{{ $homeValue('inicio_scroll_text') }}" maxlength="60" />
        </label>
      </div>

      <div class="page-editor-grid homepage-cta-grid">
        <label class="page-editor-field">
          Botón izquierdo
          <input type="text" name="inicio_left_button_text" value="{{ $homeValue('inicio_left_button_text') }}" maxlength="80" />
          <small>Ejemplo: Ver publicaciones</small>
        </label>
        <label class="page-editor-field">
          Destino del botón izquierdo
          <input type="text" name="inicio_left_button_url" value="{{ $homeValue('inicio_left_button_url') }}" maxlength="2048" placeholder="#publicaciones" />
          <small>Usa un ancla (#seccion), una ruta interna o un enlace HTTPS.</small>
        </label>
        <label class="page-editor-field">
          Botón derecho
          <input type="text" name="inicio_right_button_text" value="{{ $homeValue('inicio_right_button_text') }}" maxlength="80" />
          <small>Ejemplo: Conócenos</small>
        </label>
        <label class="page-editor-field">
          Destino del botón derecho
          <input type="text" name="inicio_right_button_url" value="{{ $homeValue('inicio_right_button_url') }}" maxlength="2048" placeholder="#quienes-somos" />
          <small>Usa un ancla (#seccion), una ruta interna o un enlace HTTPS.</small>
        </label>
      </div>

      <h3 class="homepage-editor-subheading">Cifras de la institución</h3>
      <div class="homepage-stats-editor">
        @foreach(['students' => 'Estudiantes', 'teachers' => 'Docentes', 'years' => 'Años de historia', 'projects' => 'Proyectos activos'] as $stat => $defaultLabel)
          <div class="homepage-stat-editor">
            <label class="page-editor-field">
              Número · {{ $defaultLabel }}
              <input type="number" name="inicio_{{ $stat }}_value" min="0" value="{{ $homeValue('inicio_'.$stat.'_value') }}" />
            </label>
            <label class="page-editor-field">
              Etiqueta
              <input type="text" name="inicio_{{ $stat }}_label" value="{{ $homeValue('inicio_'.$stat.'_label') }}" maxlength="60" />
            </label>
          </div>
        @endforeach
      </div>

      <h3 class="homepage-editor-subheading">Fondo, logotipo y video</h3>
      <div class="page-editor-grid homepage-media-grid">
        <label class="page-editor-field">
          Imagen de fondo
          <input type="file" name="inicio_image" accept="image/jpeg,image/png,image/webp,image/gif" data-home-preview="image" />
          <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
          @if(!empty($homeSettings['inicio_image']))
            <span class="homepage-current-media">Imagen actual guardada</span>
            <span class="homepage-remove-option"><input type="checkbox" name="remove_inicio_image" value="1" /> Quitar imagen actual</span>
          @endif
        </label>
        <div class="page-editor-image-preview homepage-image-preview">
          <span><i class="fas fa-image" aria-hidden="true"></i> Vista previa del fondo</span>
          <img src="{{ $homeSettings['inicio_image'] ?? '' }}" alt="Vista previa del fondo de portada" {{ empty($homeSettings['inicio_image']) ? 'hidden' : '' }} />
        </div>
        <label class="page-editor-field">
          Logotipo en la portada
          <input type="file" name="inicio_logo" accept="image/jpeg,image/png,image/webp,image/gif" data-home-preview="logo" />
          <small>Imagen cuadrada recomendada; máximo 2 MB.</small>
          @if(!empty($homeSettings['inicio_logo']))
            <span class="homepage-current-media">Logotipo actual guardado</span>
            <span class="homepage-remove-option"><input type="checkbox" name="remove_inicio_logo" value="1" /> Usar el logotipo institucional</span>
          @endif
        </label>
        <div class="page-editor-image-preview homepage-logo-preview">
          <span><i class="fas fa-school" aria-hidden="true"></i> Vista previa del logotipo</span>
          <img src="{{ $homeSettings['inicio_logo'] ?? '' }}" alt="Vista previa del logotipo de portada" {{ empty($homeSettings['inicio_logo']) ? 'hidden' : '' }} />
        </div>
        <label class="page-editor-field homepage-video-url">
          Enlace de video de fondo
          <input type="url" name="inicio_video_url" value="{{ old('inicio_video_url', $currentVideoUrl) }}" maxlength="2048" placeholder="https://youtu.be/... o https://.../video.mp4" />
          <small>YouTube, Vimeo o archivo MP4/WEBM directo por HTTPS.</small>
          @if($currentVideo || $currentVideoUrl)
            <span class="homepage-remove-option"><input type="checkbox" name="remove_inicio_video" value="1" /> Quitar video actual</span>
          @endif
        </label>
        <label class="page-editor-field">
          O subir un video
          <input type="file" name="inicio_video_file" accept="video/mp4,video/webm" data-home-preview="video" />
          <small>MP4 o WEBM; máximo 50 MB. Si subes uno, reemplazará el enlace.</small>
        </label>
        <div class="homepage-video-preview" aria-live="polite">
          <span><i class="fas fa-film" aria-hidden="true"></i> Vista previa del video</span>
          @if(isset($homeSettings['video_presentation']['type']) && $homeSettings['video_presentation']['type'] === 'file')
            <video src="{{ $homeSettings['video_presentation']['url'] }}" controls muted playsinline></video>
          @elseif(isset($homeSettings['video_presentation']['type']) && $homeSettings['video_presentation']['type'] === 'embed')
            <iframe src="{{ $homeSettings['video_presentation']['url'] }}" title="Vista previa del video de portada" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
          @endif
        </div>
      </div>
    </fieldset>

    <div class="page-content-editor-field" id="page-content-field" {{ $isHomepage ? 'hidden' : '' }}>
      <label class="page-editor-field" for="page-content">Contenido de la página</label>
      <textarea name="content" id="page-content" rows="16" maxlength="50000" aria-label="Contenido de la página">{{ old('content', $page->content ?? '') }}</textarea>
      <small class="page-editor-help">Puedes usar títulos, enlaces, listas y tablas. El contenido se limpia para proteger el sitio.</small>
    </div>

    <div class="page-editor-grid page-editor-bottom-grid" id="featured-image-field" {{ $isHomepage ? 'hidden' : '' }}>
      <label class="page-editor-field">
        Imagen destacada (opcional)
        <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp,image/gif" />
        <small>Formatos JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
      </label>
      <div class="page-editor-image-preview">
        @if($editing && $page->featured_image)
          <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->featured_image) }}" alt="Imagen actual de {{ $page->title }}" />
        @else
          <span><i class="fas fa-image" aria-hidden="true"></i> Vista previa de imagen</span>
          <img src="" alt="Vista previa de imagen seleccionada" hidden />
        @endif
      </div>
    </div>

    <label class="page-editor-publish">
      <input type="checkbox" name="published" value="1" {{ old('published', $page->published ?? $homepageMode) ? 'checked' : '' }} />
      <span><strong>Publicar esta página</strong><small>Al activarlo, será visible en su sección y en el menú público.</small></span>
    </label>

    <div class="admin-actions-row page-editor-actions">
      <button class="btn btn-primary" type="submit">
        <i class="fas fa-save" aria-hidden="true"></i> {{ $editing ? 'Guardar cambios' : 'Crear página' }}
      </button>
      @if($editing && $page->published)
        <a href="{{ route('pages.show', $page->slug) }}" class="btn btn-outline-dark" target="_blank" rel="noopener noreferrer">Ver página pública</a>
      @endif
      <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-dark">Cancelar</a>
    </div>
  </form>
</div>

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const title = document.getElementById('page-title');
      const slug = document.getElementById('page-slug');
      const homeFields = document.getElementById('homepage-fields');
      const contentField = document.getElementById('page-content-field');
      const featuredImageField = document.getElementById('featured-image-field');
      let slugEdited = {{ old('slug', $page->slug ?? ($homepageMode ? 'inicio' : '')) ? 'true' : 'false' }};

      function toggleHomepageEditor() {
        const isHomepage = slug?.value.trim().toLowerCase() === 'inicio';
        if (homeFields) homeFields.hidden = !isHomepage;
        if (contentField) contentField.hidden = isHomepage;
        if (featuredImageField) featuredImageField.hidden = isHomepage;
        const section = document.querySelector('select[name="section"]');
        if (isHomepage && section) section.value = 'inicio';
        else if (section?.value === 'inicio') section.value = 'institucional';
      }

      slug?.addEventListener('input', () => { slugEdited = true; });
      title?.addEventListener('input', () => {
        if (!slugEdited && slug) {
          slug.value = title.value.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
        toggleHomepageEditor();
      });
      slug?.addEventListener('input', toggleHomepageEditor);
      toggleHomepageEditor();

      const imageInput = document.querySelector('input[name="featured_image"]');
      imageInput?.addEventListener('change', () => {
        const image = imageInput.files?.[0];
        const preview = document.querySelector('.page-editor-image-preview img');
        if (!image || !preview) return;
        preview.src = URL.createObjectURL(image);
        preview.hidden = false;
        preview.parentElement.querySelector('span')?.remove();
      });

      document.querySelectorAll('[data-home-preview]').forEach((input) => {
        input.addEventListener('change', () => {
          const file = input.files?.[0];
          const type = input.dataset.homePreview;
          if (!file || !type) return;
          const preview = document.querySelector(type === 'video' ? '.homepage-video-preview' : `.homepage-${type}-preview`);
          if (!preview) return;
          preview.replaceChildren();
          if (type === 'video') {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.controls = true;
            video.muted = true;
            video.playsInline = true;
            preview.append(video);
          } else {
            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = type === 'logo' ? 'Vista previa del logotipo' : 'Vista previa de la imagen de fondo';
            preview.append(image);
          }
        });
      });

    });
  </script>
@endpush
