@extends('layouts.admin')

@php
  $adminSection = 'pages';
  $isAboutSection = $section === 'quienes-somos';
  $isProjectSection = $section === 'proyecto';
  $isPublicationsSection = $section === 'publicaciones';
  $aboutFieldGroups = [
      [
          'title' => 'Presentación',
          'description' => 'Define cómo se presenta la institución y el texto de bienvenida.',
          'icon' => 'fa-landmark',
          'fields' => ['eyebrow', 'title', 'intro_heading', 'intro_first', 'intro_second'],
      ],
      [
          'title' => 'Identidad y fortalezas',
          'description' => 'Destaca el lema y los atributos que representan a la comunidad educativa.',
          'icon' => 'fa-compass',
          'fields' => ['motto', 'motto_caption', 'feature_academic', 'feature_academic_icon', 'feature_values', 'feature_values_icon', 'feature_innovation', 'feature_innovation_icon', 'feature_community', 'feature_community_icon'],
      ],
      [
          'title' => 'Misión',
          'description' => 'El propósito que guía el trabajo de la institución.',
          'icon' => 'fa-bullseye',
          'fields' => ['mission_heading', 'mission_icon', 'mission'],
          'class' => 'who-admin-principle',
      ],
      [
          'title' => 'Visión',
          'description' => 'La institución que aspiramos a construir.',
          'icon' => 'fa-eye',
          'fields' => ['vision_heading', 'vision_icon', 'vision'],
          'class' => 'who-admin-principle',
      ],
      [
          'title' => 'Valores',
          'description' => 'Los principios que orientan nuestra convivencia y formación.',
          'icon' => 'fa-heart',
          'fields' => ['values_heading', 'values_icon', 'values'],
          'class' => 'who-admin-principle',
      ],
  ];
  $projectFieldGroups = [
      [
          'title' => 'Presentación del proyecto',
          'description' => 'Configura el encabezado, la descripción y el nombre que identifica el proyecto.',
          'icon' => 'fa-lightbulb',
          'fields' => ['eyebrow', 'title', 'description', 'root_label'],
          'wide' => ['description'],
      ],
      [
          'title' => 'Tarjeta principal',
          'description' => 'El contenido destacado que invita a conocer las iniciativas de IJENT.',
          'icon' => 'fa-star',
          'fields' => ['highlight_tag', 'highlight_tag_icon', 'highlight_title', 'highlight_description'],
          'wide' => ['highlight_title', 'highlight_description'],
      ],
      [
          'title' => 'Recursos y navegación',
          'description' => 'Aclara qué encontrarán las personas y cómo pueden recorrer los recursos.',
          'icon' => 'fa-compass',
          'fields' => ['pill_1', 'pill_1_icon', 'pill_2', 'pill_2_icon', 'pill_3', 'pill_3_icon', 'explore_hint'],
          'wide' => ['explore_hint'],
      ],
      [
          'title' => 'Contenido de Google Drive',
          'description' => 'Personaliza los mensajes mientras cargan los recursos y la vista de respaldo.',
          'icon' => 'fa-cloud',
          'fields' => ['drive_loading_text', 'drive_fallback_title', 'drive_fallback_description', 'drive_open_label'],
          'wide' => ['drive_fallback_description'],
      ],
  ];
  $newsCount = $section === 'novedades'
      ? min(30, max(0, (int) old('news_count', $values['news_count'])))
      : 0;
  $deletedNews = $section === 'novedades'
      ? array_map('intval', (array) old('news_deleted', []))
      : [];
  $publicationCount = $isPublicationsSection
      ? min(30, max(0, (int) old('publication_count', $values['publication_count'] ?? 3)))
      : 0;
  $deletedPublications = $isPublicationsSection
      ? array_map('intval', (array) old('publication_deleted', []))
      : [];
@endphp

@section('admin_content')
  <div class="admin-card page-editor {{ $section === 'novedades' ? 'news-section-editor-page' : '' }} {{ $isAboutSection ? 'who-section-editor-page' : '' }} {{ $isProjectSection ? 'project-section-editor-page' : '' }} {{ $isPublicationsSection ? 'publications-admin-page' : '' }}">
    <header class="page-editor-header {{ $section === 'novedades' ? 'news-page-header' : '' }} {{ $isAboutSection ? 'who-page-header' : '' }} {{ $isProjectSection ? 'project-page-header' : '' }} {{ $isPublicationsSection ? 'publications-admin-header' : '' }}">
      <div>
        @if($isPublicationsSection)
          <span class="publications-admin-kicker"><i class="fas fa-book-open" aria-hidden="true"></i> Biblioteca institucional</span>
        @elseif($isProjectSection)
          <span class="project-admin-kicker"><i class="fas fa-lightbulb" aria-hidden="true"></i> Proyecto IJENT</span>
        @elseif($isAboutSection)
          <span class="who-admin-kicker"><i class="fas fa-feather-alt" aria-hidden="true"></i> Identidad institucional</span>
        @elseif($section === 'novedades')
          <span class="news-admin-kicker"><i class="fas fa-sparkles" aria-hidden="true"></i> Editor de contenido</span>
        @else
          <span class="page-manager-eyebrow">Contenido del sitio</span>
        @endif
        <h2 class="section-title">Editar {{ $definition['label'] }}</h2>
        <p>{{ $definition['description'] }}</p>
      </div>
      <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-dark">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a páginas
      </a>
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
          action="{{ route('admin.pages.sections.update', $section) }}"
          class="admin-form-stack page-editor-form"
          enctype="multipart/form-data">
      @csrf
      @method('PUT')

      @if($isAboutSection)
        <div class="who-admin-content">
          <div class="who-admin-overview">
            <div class="who-admin-overview-mark" aria-hidden="true"><i class="fas fa-book-open"></i></div>
            <div>
              <span class="who-admin-overview-label">Un espacio para contar quiénes somos</span>
              <p>Organiza la historia, el propósito y las imágenes de la institución. Los cambios se reflejarán en la página pública al guardar.</p>
            </div>
          </div>

          <div class="who-admin-fields">
            @foreach($aboutFieldGroups as $group)
              <section class="who-admin-group {{ $group['class'] ?? '' }}" aria-labelledby="who-admin-group-{{ $loop->index }}">
                <header class="who-admin-group-heading">
                  <span class="who-admin-group-icon" aria-hidden="true"><i class="fas {{ $group['icon'] }}"></i></span>
                  <div>
                    <h3 id="who-admin-group-{{ $loop->index }}">{{ $group['title'] }}</h3>
                    <p>{{ $group['description'] }}</p>
                  </div>
                </header>
                <div class="page-editor-grid who-admin-group-grid">
                  @foreach($group['fields'] as $field)
                    @php
                      $config = $definition['fields'][$field];
                      $isTextarea = ($config['type'] ?? 'text') === 'textarea';
                      $isWideField = in_array($field, ['intro_first', 'intro_second', 'mission', 'vision', 'values'], true);
                    @endphp
                    <label class="page-editor-field who-admin-field {{ $isWideField ? 'who-admin-field-wide' : '' }}">
                      {{ $config['label'] }}
                      @if(($config['type'] ?? null) === 'icon')
                        <span class="publication-icon-picker">
                          <select name="{{ $field }}" data-shared-icon-select data-icon-choice>
                            @foreach($iconChoices as $icon => $label)
                              <option value="{{ $icon }}" {{ old($field, $values[$field]) === $icon ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                          </select>
                          <span class="publication-icon-preview" aria-hidden="true"><i class="fas {{ old($field, $values[$field]) }}" data-shared-icon-preview data-icon-preview></i></span>
                        </span>
                      @elseif($isTextarea)
                        <textarea name="{{ $field }}" rows="4" maxlength="{{ $config['max'] }}">{{ old($field, $values[$field]) }}</textarea>
                      @else
                        <input type="text" name="{{ $field }}" value="{{ old($field, $values[$field]) }}" maxlength="{{ $config['max'] }}" />
                      @endif
                    </label>
                  @endforeach
                </div>
              </section>
            @endforeach
          </div>
        </div>
      @elseif($isProjectSection)
        <div class="project-admin-content">
          <div class="project-admin-overview">
            <div class="project-admin-overview-mark" aria-hidden="true"><i class="fas fa-folder-open"></i></div>
            <div>
              <span class="project-admin-overview-label">Un proyecto que crece con su comunidad</span>
              <p>Presenta la iniciativa, destaca sus recursos y ofrece una guía clara para explorar el contenido compartido.</p>
            </div>
          </div>

          <div class="project-admin-fields">
            @foreach($projectFieldGroups as $group)
              <section class="project-admin-group" aria-labelledby="project-admin-group-{{ $loop->index }}">
                <header class="project-admin-group-heading">
                  <span class="project-admin-group-icon" aria-hidden="true"><i class="fas {{ $group['icon'] }}"></i></span>
                  <div>
                    <h3 id="project-admin-group-{{ $loop->index }}">{{ $group['title'] }}</h3>
                    <p>{{ $group['description'] }}</p>
                  </div>
                </header>
                <div class="page-editor-grid project-admin-group-grid">
                  @foreach($group['fields'] as $field)
                    @php
                      $config = $definition['fields'][$field];
                      $isTextarea = ($config['type'] ?? 'text') === 'textarea';
                      $isWideField = in_array($field, $group['wide'], true);
                    @endphp
                    <label class="page-editor-field project-admin-field {{ $isWideField ? 'project-admin-field-wide' : '' }}">
                      {{ $config['label'] }}
                      @if(($config['type'] ?? null) === 'icon')
                        <span class="publication-icon-picker">
                          <select name="{{ $field }}" data-shared-icon-select data-icon-choice>
                            @foreach($iconChoices as $icon => $label)
                              <option value="{{ $icon }}" {{ old($field, $values[$field]) === $icon ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                          </select>
                          <span class="publication-icon-preview" aria-hidden="true"><i class="fas {{ old($field, $values[$field]) }}" data-shared-icon-preview data-icon-preview></i></span>
                        </span>
                      @elseif($isTextarea)
                        <textarea name="{{ $field }}" rows="4" maxlength="{{ $config['max'] }}">{{ old($field, $values[$field]) }}</textarea>
                      @else
                        <input type="text" name="{{ $field }}" value="{{ old($field, $values[$field]) }}" maxlength="{{ $config['max'] }}" />
                      @endif
                    </label>
                  @endforeach
                </div>
              </section>
            @endforeach
          </div>
        </div>
      @elseif($isPublicationsSection)
        <div class="publications-admin-content">
          <div class="publications-admin-overview">
            <div class="publications-admin-overview-mark" aria-hidden="true"><i class="fas fa-calendar-alt"></i></div>
            <div>
              <span class="publications-admin-overview-label">Planifica lo que la comunidad verá</span>
              <p>Agrega publicaciones con su propia imagen y programa sus fechas. Si dejas el periodo vacío, seguirá visible sin límite.</p>
            </div>
          </div>

          <section class="publications-admin-identity" aria-labelledby="publications-admin-identity-title">
            <header class="publications-admin-group-heading">
              <span class="publications-admin-group-icon" aria-hidden="true"><i class="fas fa-book-open"></i></span>
              <div>
                <h3 id="publications-admin-identity-title">Identidad de la sección</h3>
                <p>El encabezado que presenta los recursos y experiencias de la comunidad.</p>
              </div>
            </header>
            <div class="page-editor-grid publications-admin-group-grid">
              @foreach(['eyebrow', 'title'] as $field)
                @php
                  $config = $definition['fields'][$field];
                @endphp
                <label class="page-editor-field publications-admin-field">
                  {{ $config['label'] }}
                  <input type="text" name="{{ $field }}" value="{{ old($field, $values[$field]) }}" maxlength="{{ $config['max'] }}" />
                </label>
              @endforeach
            </div>
          </section>

          <section class="publications-admin-editor news-admin-editor" aria-labelledby="publications-admin-editor-title">
            <div class="news-admin-editor-heading publications-admin-editor-heading">
              <div>
                <span class="publications-admin-kicker"><i class="fas fa-images" aria-hidden="true"></i> Administración</span>
                <h3 id="publications-admin-editor-title">Publicaciones del carrusel</h3>
                <p>Cada publicación conserva su imagen, descripción y programación.</p>
                <div class="news-admin-summary">
                  <span><i class="fas fa-layer-group" aria-hidden="true"></i> <strong id="publicationVisibleCount">{{ $publicationCount }}</strong> publicaciones activas</span>
                  <span><i class="fas fa-calendar-check" aria-hidden="true"></i> Hasta 30 publicaciones</span>
                </div>
              </div>
              <button class="btn btn-primary news-admin-add-button" type="button" id="addPublicationItem" aria-haspopup="dialog" {{ $publicationCount >= 30 ? 'disabled' : '' }}>
                <i class="fas fa-plus" aria-hidden="true"></i> Nueva publicación
              </button>
            </div>
            <input type="hidden" name="publication_count" id="publicationCount" value="{{ $publicationCount }}" />
            @foreach($deletedPublications as $deletedIndex)
              <input type="hidden" name="publication_deleted[]" value="{{ $deletedIndex }}" />
            @endforeach

            <div class="news-admin-editor-list publications-admin-editor-list" id="publicationItems">
              @for($number = 1; $number <= $publicationCount; $number++)
                @php
                  $titleField = 'slide_'.$number.'_title';
                  $descriptionField = 'slide_'.$number.'_description';
                  $iconField = 'slide_'.$number.'_icon';
                  $startsField = 'slide_'.$number.'_starts_at';
                  $endsField = 'slide_'.$number.'_ends_at';
                  $imageField = 'slide_'.$number.'_image';
                @endphp
                @if(filled(old($titleField, $values[$titleField])) && !in_array($number, $deletedPublications, true))
                  <article class="news-admin-item publications-admin-item" data-publication-item data-publication-index="{{ $number }}">
                    <div class="news-admin-item-top">
                      <span class="news-admin-index">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</span>
                      <span class="news-admin-item-label">Publicación</span>
                      <button class="news-admin-delete" type="button" data-remove-publication aria-label="Eliminar publicación {{ $number }}">
                        <i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar
                      </button>
                    </div>
                    <details class="news-admin-card-details" {{ $number === 1 ? 'open' : '' }}>
                      <summary class="news-admin-card-summary">
                        <span class="news-admin-card-title" data-publication-summary-title>{{ old($titleField, $values[$titleField]) }}</span>
                        <span class="news-admin-card-meta" data-publication-summary-meta>Publicación {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</span>
                        <i class="fas fa-chevron-down news-admin-card-chevron" aria-hidden="true"></i>
                      </summary>
                      <div class="news-admin-card-content">
                        <div class="page-editor-grid">
                          <label class="page-editor-field news-admin-wide">
                            Título
                            <input type="text" name="{{ $titleField }}" value="{{ old($titleField, $values[$titleField]) }}" maxlength="200" required data-publication-field="title" />
                          </label>
                          <label class="page-editor-field">
                            Icono de la publicación
                            <span class="publication-icon-picker">
                              <select name="{{ $iconField }}" data-publication-field="icon" data-publication-icon-select data-icon-choice>
                                @foreach($iconChoices as $icon => $label)
                                  <option value="{{ $icon }}" {{ old($iconField, $values[$iconField]) === $icon ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                              </select>
                              <span class="publication-icon-preview" aria-hidden="true"><i class="fas {{ old($iconField, $values[$iconField]) }}" data-publication-icon-preview data-icon-preview></i></span>
                            </span>
                          </label>
                          <label class="page-editor-field news-admin-wide">
                            Descripción
                            <textarea name="{{ $descriptionField }}" rows="3" maxlength="1000" data-publication-field="description">{{ old($descriptionField, $values[$descriptionField]) }}</textarea>
                          </label>
                          <label class="page-editor-field">
                            Mostrar desde
                            <input type="date" name="{{ $startsField }}" value="{{ old($startsField, $values[$startsField]) }}" data-publication-field="starts_at" />
                          </label>
                          <label class="page-editor-field">
                            Mostrar hasta
                            <input type="date" name="{{ $endsField }}" value="{{ old($endsField, $values[$endsField]) }}" data-publication-field="ends_at" />
                          </label>
                          <label class="page-editor-field news-admin-wide">
                            Imagen de publicación {{ $number }}
                            <input type="file" name="media_{{ $imageField }}" accept="image/jpeg,image/png,image/webp,image/gif" data-section-preview="{{ $imageField }}" />
                            <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
                            @if(!empty($values['stored_media'][$imageField]))
                              <span class="homepage-remove-option">
                                <input type="checkbox" name="remove_{{ $imageField }}" value="1" />
                                Usar la imagen predeterminada
                              </span>
                            @endif
                            <img class="news-admin-image-preview publications-admin-item-preview" src="{{ $values['media'][$imageField] }}" alt="Imagen de la publicación {{ $number }}" data-section-image="{{ $imageField }}" />
                          </label>
                        </div>
                      </div>
                    </details>
                  </article>
                @endif
              @endfor
            </div>

            <template id="publicationItemTemplate">
              <article class="news-admin-item publications-admin-item" data-publication-item data-publication-index="__INDEX__">
                <div class="news-admin-item-top">
                  <span class="news-admin-index">__PADDED_INDEX__</span>
                  <span class="news-admin-item-label">Publicación</span>
                  <button class="news-admin-delete" type="button" data-remove-publication aria-label="Eliminar publicación __INDEX__">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar
                  </button>
                </div>
                <details class="news-admin-card-details" open>
                  <summary class="news-admin-card-summary">
                    <span class="news-admin-card-title" data-publication-summary-title>Nueva publicación</span>
                    <span class="news-admin-card-meta" data-publication-summary-meta>Siempre visible</span>
                    <i class="fas fa-chevron-down news-admin-card-chevron" aria-hidden="true"></i>
                  </summary>
                  <div class="news-admin-card-content">
                    <div class="page-editor-grid">
                      <label class="page-editor-field news-admin-wide">
                        Título
                        <input type="text" maxlength="200" required data-publication-field="title" />
                      </label>
                      <label class="page-editor-field">
                        Icono de la publicación
                        <span class="publication-icon-picker">
                          <select data-publication-field="icon" data-publication-icon-select data-icon-choice>
                            @foreach($iconChoices as $icon => $label)
                              <option value="{{ $icon }}">{{ $label }}</option>
                            @endforeach
                          </select>
                          <span class="publication-icon-preview" aria-hidden="true"><i class="fas fa-book-open" data-publication-icon-preview data-icon-preview></i></span>
                        </span>
                      </label>
                      <label class="page-editor-field news-admin-wide">
                        Descripción
                        <textarea rows="3" maxlength="1000" data-publication-field="description"></textarea>
                      </label>
                      <label class="page-editor-field">
                        Mostrar desde
                        <input type="date" data-publication-field="starts_at" />
                      </label>
                      <label class="page-editor-field">
                        Mostrar hasta
                        <input type="date" data-publication-field="ends_at" />
                      </label>
                      <label class="page-editor-field news-admin-wide">
                        Imagen de la publicación
                        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-publication-image />
                        <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
                        <img class="news-admin-image-preview publications-admin-item-preview" src="{{ asset('images/1.jpeg') }}" alt="Vista previa de la nueva publicación" />
                      </label>
                    </div>
                  </div>
                </details>
              </article>
            </template>
          </section>
        </div>

        <div class="news-admin-modal publications-admin-modal" id="publicationAddModal" aria-hidden="true">
          <div class="news-admin-modal-backdrop" data-close-publication-modal></div>
          <section class="news-admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="publicationAddModalTitle">
            <header class="news-admin-modal-heading">
              <div>
                <span class="publications-admin-kicker">Biblioteca institucional</span>
                <h3 id="publicationAddModalTitle">Agregar publicación</h3>
                <p>Completa el contenido y asocia la imagen para el carrusel.</p>
              </div>
              <button type="button" class="news-admin-modal-close" aria-label="Cerrar" data-close-publication-modal>&times;</button>
            </header>
            <fieldset class="page-editor-grid news-admin-modal-fields publications-admin-modal-fields" disabled>
              <label class="page-editor-field news-admin-wide">
                Título
                <input type="text" data-modal-publication-field="title" maxlength="200" required />
              </label>
              <label class="page-editor-field">
                Icono de la publicación
                <span class="publication-icon-picker">
                  <select data-modal-publication-field="icon" data-icon-choice>
                    @foreach($iconChoices as $icon => $label)
                      <option value="{{ $icon }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <span class="publication-icon-preview" aria-hidden="true"><i class="fas fa-book-open" data-modal-publication-icon-preview data-icon-preview></i></span>
                </span>
              </label>
              <label class="page-editor-field news-admin-wide">
                Descripción
                <textarea data-modal-publication-field="description" rows="4" maxlength="1000"></textarea>
              </label>
              <label class="page-editor-field">
                Mostrar desde
                <input type="date" data-modal-publication-field="starts_at" />
              </label>
              <label class="page-editor-field">
                Mostrar hasta
                <input type="date" data-modal-publication-field="ends_at" />
              </label>
              <label class="page-editor-field news-admin-wide">
                Imagen
                <input type="file" data-modal-publication-image accept="image/jpeg,image/png,image/webp,image/gif" />
                <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
              </label>
            </fieldset>
            <footer class="news-admin-modal-actions">
              <button type="button" class="btn btn-outline-dark" data-close-publication-modal>Cancelar</button>
              <button type="button" class="btn btn-primary" id="savePublicationItem">
                <i class="fas fa-plus" aria-hidden="true"></i> Agregar publicación
              </button>
            </footer>
          </section>
        </div>
      @else
      <div class="page-editor-grid {{ $section === 'novedades' ? 'news-admin-intro-grid' : '' }}">
        @foreach($definition['fields'] as $field => $config)
          @if($section === 'novedades' && ($field === 'news_count' || preg_match('/^news_\d+_/', $field)))
            @continue
          @endif
          <label class="page-editor-field {{ ($config['type'] ?? 'text') === 'textarea' ? 'page-editor-slug' : '' }}">
            {{ $config['label'] }}
            @if(($config['type'] ?? 'text') === 'textarea')
              <textarea name="{{ $field }}" rows="4" maxlength="{{ $config['max'] }}">{{ old($field, $values[$field]) }}</textarea>
            @else
              <input type="text" name="{{ $field }}" value="{{ old($field, $values[$field]) }}" maxlength="{{ $config['max'] }}" />
            @endif
          </label>
        @endforeach
      </div>
      @endif

      @if($section === 'novedades')
        <section class="news-admin-editor" aria-labelledby="news-admin-editor-title">
          <div class="news-admin-editor-heading">
            <div>
              <span class="news-admin-kicker">Administración</span>
              <h3 id="news-admin-editor-title">Tarjetas de novedades</h3>
              <p>Programa cuándo se muestra cada noticia. Deja las fechas vacías para mantenerla siempre visible.</p>
              <div class="news-admin-summary">
                <span><i class="fas fa-layer-group" aria-hidden="true"></i> <strong id="newsVisibleCount">{{ $newsCount }}</strong> tarjetas activas</span>
                <span><i class="fas fa-calendar-check" aria-hidden="true"></i> Hasta 30 novedades</span>
              </div>
            </div>
            <button class="btn btn-primary news-admin-add-button" type="button" id="addNewsItem" aria-haspopup="dialog" {{ $newsCount >= 30 ? 'disabled' : '' }}>
              <i class="fas fa-plus" aria-hidden="true"></i> Nueva novedad
            </button>
          </div>
          <input type="hidden" name="news_count" id="newsCount" value="{{ $newsCount }}" />
          @foreach($deletedNews as $deletedIndex)
            <input type="hidden" name="news_deleted[]" value="{{ $deletedIndex }}" />
          @endforeach

          <div class="news-admin-editor-list" id="newsItems">
            @for($number = 1; $number <= $newsCount; $number++)
              @php
                $dateField = 'news_'.$number.'_date';
                $titleField = 'news_'.$number.'_title';
                $excerptField = 'news_'.$number.'_excerpt';
                $tagField = 'news_'.$number.'_tag';
                $iconField = 'news_'.$number.'_icon';
                $startsField = 'news_'.$number.'_starts_at';
                $endsField = 'news_'.$number.'_ends_at';
                $imageField = 'news_'.$number.'_image';
              @endphp
              @if(filled(old($titleField, $values[$titleField])) && !in_array($number, $deletedNews, true))
              <article class="news-admin-item" data-news-item data-news-index="{{ $number }}">
                <div class="news-admin-item-top">
                  <span class="news-admin-index">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</span>
                  <span class="news-admin-item-label">Novedad</span>
                  <button class="news-admin-delete" type="button" data-remove-news aria-label="Eliminar novedad {{ $number }}">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar
                  </button>
                </div>
                <details class="news-admin-card-details" {{ $number === 1 ? 'open' : '' }}>
                  <summary class="news-admin-card-summary">
                    <span class="news-admin-card-title" data-news-summary-title>{{ old($titleField, $values[$titleField]) }}</span>
                    <span class="news-admin-card-meta" data-news-summary-meta>{{ old($tagField, $values[$tagField]) ?: 'Sin categoría' }} · {{ old($startsField, $values[$startsField]) ?: 'Siempre visible' }}</span>
                    <i class="fas fa-chevron-down news-admin-card-chevron" aria-hidden="true"></i>
                  </summary>
                  <div class="news-admin-card-content">
                  <div class="page-editor-grid">
                  <label class="page-editor-field">
                    Fecha que se muestra
                    <input type="text" name="{{ $dateField }}" value="{{ old($dateField, $values[$dateField]) }}" maxlength="80" placeholder="Ej. 24 de octubre de 2026" />
                  </label>
                  <label class="page-editor-field">
                    Categoría
                    <input type="text" name="{{ $tagField }}" value="{{ old($tagField, $values[$tagField]) }}" maxlength="80" />
                  </label>
                  <label class="page-editor-field">
                    Icono de la novedad
                    <span class="publication-icon-picker">
                      <select name="{{ $iconField }}" data-icon-choice>
                        @foreach($iconChoices as $icon => $label)
                          <option value="{{ $icon }}" {{ old($iconField, $values[$iconField]) === $icon ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                      </select>
                      <span class="publication-icon-preview" aria-hidden="true"><i class="fas {{ old($iconField, $values[$iconField]) }}" data-icon-preview></i></span>
                    </span>
                  </label>
                  <label class="page-editor-field news-admin-wide">
                    Título
                    <input type="text" name="{{ $titleField }}" value="{{ old($titleField, $values[$titleField]) }}" maxlength="200" required />
                  </label>
                  <label class="page-editor-field news-admin-wide">
                    Descripción
                    <textarea name="{{ $excerptField }}" rows="3" maxlength="1000">{{ old($excerptField, $values[$excerptField]) }}</textarea>
                  </label>
                  <label class="page-editor-field">
                    Mostrar desde
                    <input type="date" name="{{ $startsField }}" value="{{ old($startsField, $values[$startsField]) }}" />
                  </label>
                  <label class="page-editor-field">
                    Mostrar hasta
                    <input type="date" name="{{ $endsField }}" value="{{ old($endsField, $values[$endsField]) }}" />
                  </label>
                  <label class="page-editor-field news-admin-wide">
                    Imagen
                    <input type="file" name="media_{{ $imageField }}" accept="image/jpeg,image/png,image/webp,image/gif" data-section-preview="{{ $imageField }}" />
                    <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
                    @if(!empty($values['stored_media'][$imageField]))
                      <span class="homepage-remove-option">
                        <input type="checkbox" name="remove_{{ $imageField }}" value="1" />
                        Usar la imagen predeterminada
                      </span>
                    @endif
                    <img class="news-admin-image-preview" src="{{ $values['media'][$imageField] }}" alt="Imagen de la novedad {{ $number }}" data-section-image="{{ $imageField }}" />
                  </label>
                  </div>
                  </div>
                </details>
              </article>
              @endif
            @endfor
          </div>

          <template id="newsItemTemplate">
            <article class="news-admin-item" data-news-item data-news-index="__INDEX__">
              <div class="news-admin-item-top">
                <span class="news-admin-index">__PADDED_INDEX__</span>
                <span class="news-admin-item-label">Novedad</span>
                <button class="news-admin-delete" type="button" data-remove-news aria-label="Eliminar novedad __INDEX__">
                  <i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar
                </button>
              </div>
              <details class="news-admin-card-details" open>
                <summary class="news-admin-card-summary">
                  <span class="news-admin-card-title" data-news-summary-title>Nueva novedad</span>
                  <span class="news-admin-card-meta" data-news-summary-meta>Sin categoría · Siempre visible</span>
                  <i class="fas fa-chevron-down news-admin-card-chevron" aria-hidden="true"></i>
                </summary>
                <div class="news-admin-card-content">
                <div class="page-editor-grid">
                <label class="page-editor-field">
                  Fecha que se muestra
                  <input type="text" data-news-field="date" maxlength="80" placeholder="Ej. 24 de octubre de 2026" />
                </label>
                <label class="page-editor-field">
                  Categoría
                  <input type="text" data-news-field="tag" maxlength="80" />
                </label>
                <label class="page-editor-field">
                  Icono de la novedad
                  <span class="publication-icon-picker">
                    <select data-news-field="icon" data-icon-choice>
                      @foreach($iconChoices as $icon => $label)
                        <option value="{{ $icon }}">{{ $label }}</option>
                      @endforeach
                    </select>
                    <span class="publication-icon-preview" aria-hidden="true"><i class="fas fa-newspaper" data-icon-preview></i></span>
                  </span>
                </label>
                <label class="page-editor-field news-admin-wide">
                  Título
                  <input type="text" data-news-field="title" maxlength="200" required />
                </label>
                <label class="page-editor-field news-admin-wide">
                  Descripción
                  <textarea data-news-field="excerpt" rows="3" maxlength="1000"></textarea>
                </label>
                <label class="page-editor-field">
                  Mostrar desde
                  <input type="date" data-news-field="starts_at" />
                </label>
                <label class="page-editor-field">
                  Mostrar hasta
                  <input type="date" data-news-field="ends_at" />
                </label>
                <label class="page-editor-field news-admin-wide">
                  Imagen
                  <input type="file" data-news-image accept="image/jpeg,image/png,image/webp,image/gif" />
                  <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
                  <img class="news-admin-image-preview" src="{{ asset('images/1.jpeg') }}" alt="Vista previa de la nueva novedad" />
                </label>
                </div>
                </div>
              </details>
            </article>
          </template>

          <div class="news-admin-modal" id="newsAddModal" aria-hidden="true">
            <div class="news-admin-modal-backdrop" data-close-news-modal></div>
            <section class="news-admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="newsAddModalTitle">
              <header class="news-admin-modal-heading">
                <div>
                  <span class="page-manager-eyebrow">Contenido del sitio</span>
                  <h3 id="newsAddModalTitle">Agregar novedad</h3>
                  <p>Completa la información y elige cuándo debe aparecer.</p>
                </div>
                <button type="button" class="news-admin-modal-close" aria-label="Cerrar" data-close-news-modal>&times;</button>
              </header>
              <fieldset class="page-editor-grid news-admin-modal-fields" disabled>
                <label class="page-editor-field">
                  Título
                  <input type="text" data-modal-news-field="title" maxlength="200" required />
                </label>
                <label class="page-editor-field">
                  Categoría
                  <input type="text" data-modal-news-field="tag" maxlength="80" />
                </label>
                <label class="page-editor-field">
                  Icono de la novedad
                  <span class="publication-icon-picker">
                    <select data-modal-news-field="icon" data-icon-choice>
                      @foreach($iconChoices as $icon => $label)
                        <option value="{{ $icon }}">{{ $label }}</option>
                      @endforeach
                    </select>
                    <span class="publication-icon-preview" aria-hidden="true"><i class="fas fa-newspaper" data-icon-preview></i></span>
                  </span>
                </label>
                <label class="page-editor-field news-admin-wide">
                  Descripción
                  <textarea data-modal-news-field="excerpt" rows="4" maxlength="1000"></textarea>
                </label>
                <label class="page-editor-field">
                  Fecha que se muestra
                  <input type="text" data-modal-news-field="date" maxlength="80" placeholder="Ej. 24 de octubre de 2026" />
                </label>
                <label class="page-editor-field">
                  Mostrar desde
                  <input type="date" data-modal-news-field="starts_at" />
                </label>
                <label class="page-editor-field">
                  Mostrar hasta
                  <input type="date" data-modal-news-field="ends_at" />
                </label>
                <label class="page-editor-field news-admin-wide">
                  Imagen
                  <input type="file" data-modal-news-image accept="image/jpeg,image/png,image/webp,image/gif" />
                  <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
                </label>
              </fieldset>
              <footer class="news-admin-modal-actions">
                <button type="button" class="btn btn-outline-dark" data-close-news-modal>Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveNewsItem">
                  <i class="fas fa-plus" aria-hidden="true"></i> Agregar tarjeta
                </button>
              </footer>
            </section>
          </div>
        </section>
      @elseif($isProjectSection && count($definition['media']))
        <section class="project-admin-media" aria-labelledby="project-admin-media-title">
          <header class="project-admin-media-heading">
            <span class="project-admin-group-icon" aria-hidden="true"><i class="fas fa-image"></i></span>
            <div>
              <h3 id="project-admin-media-title">Imagen destacada</h3>
              <p>Elige una imagen que represente el proyecto y revisa cómo aparecerá antes de guardar.</p>
            </div>
          </header>
          @foreach($definition['media'] as $field => $config)
            <article class="project-admin-media-card">
              <div class="project-admin-image-preview">
                <img src="{{ $values['media'][$field] }}" alt="Vista previa: {{ $config['label'] }}" data-section-image="{{ $field }}" />
              </div>
              <div class="project-admin-media-controls">
                <label class="page-editor-field project-admin-field">
                  {{ $config['label'] }}
                  <input type="file" name="media_{{ $field }}" accept="image/jpeg,image/png,image/webp,image/gif" data-section-preview="{{ $field }}" />
                  <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
                  @if(!empty($values['stored_media'][$field]))
                    <span class="homepage-remove-option">
                      <input type="checkbox" name="remove_{{ $field }}" value="1" />
                      Usar la imagen predeterminada
                    </span>
                  @endif
                </label>
              </div>
            </article>
          @endforeach
        </section>
      @elseif($isAboutSection && count($definition['media']))
        <section class="who-admin-media" aria-labelledby="who-admin-media-title">
          <header class="who-admin-media-heading">
            <span class="who-admin-group-icon" aria-hidden="true"><i class="fas fa-images"></i></span>
            <div>
              <h3 id="who-admin-media-title">Fotografías e historias</h3>
              <p>Asocia cada imagen con su pie de foto para dar contexto a la historia institucional.</p>
            </div>
          </header>
          <div class="who-admin-media-grid">
            @foreach($definition['media'] as $field => $config)
              @php
                $captionField = $field === 'inline_image' ? 'inline_caption' : 'main_caption';
                $captionConfig = $definition['fields'][$captionField];
              @endphp
              <article class="who-admin-media-card">
                <div class="who-admin-media-card-heading">
                  <span class="who-admin-media-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                  <div>
                    <h4>{{ $config['label'] }}</h4>
                    <p>Imagen y descripción pública</p>
                  </div>
                </div>
                <div class="who-admin-image-preview">
                  <img src="{{ $values['media'][$field] }}" alt="Vista previa: {{ $config['label'] }}" data-section-image="{{ $field }}" />
                </div>
                <label class="page-editor-field who-admin-field">
                  Seleccionar imagen
                  <input type="file" name="media_{{ $field }}" accept="image/jpeg,image/png,image/webp,image/gif" data-section-preview="{{ $field }}" />
                  <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
                  @if(!empty($values['stored_media'][$field]))
                    <span class="homepage-remove-option">
                      <input type="checkbox" name="remove_{{ $field }}" value="1" />
                      Usar la imagen predeterminada
                    </span>
                  @endif
                </label>
                <label class="page-editor-field who-admin-field who-admin-caption-field">
                  {{ $captionConfig['label'] }}
                  <input type="text" name="{{ $captionField }}" value="{{ old($captionField, $values[$captionField]) }}" maxlength="{{ $captionConfig['max'] }}" />
                </label>
              </article>
            @endforeach
          </div>
        </section>
      @elseif($section !== 'novedades' && ! $isPublicationsSection && count($definition['media']))
        <h3 class="homepage-editor-subheading">Imágenes de la sección</h3>
        <div class="page-editor-grid homepage-media-grid">
          @foreach($definition['media'] as $field => $config)
            <label class="page-editor-field">
              {{ $config['label'] }}
              <input type="file" name="media_{{ $field }}" accept="image/jpeg,image/png,image/webp,image/gif" data-section-preview="{{ $field }}" />
              <small>JPG, PNG, WEBP o GIF; máximo 5 MB.</small>
              @if(!empty($values['stored_media'][$field]))
                <span class="homepage-remove-option">
                  <input type="checkbox" name="remove_{{ $field }}" value="1" />
                  Usar la imagen predeterminada
                </span>
              @endif
            </label>
            <div class="page-editor-image-preview">
              <span><i class="fas fa-image" aria-hidden="true"></i> {{ $config['label'] }}</span>
              <img src="{{ $values['media'][$field] }}" alt="{{ $config['label'] }}" data-section-image="{{ $field }}" />
            </div>
          @endforeach
        </div>
      @endif

      <div class="admin-actions-row page-editor-actions">
        <button class="btn btn-primary" type="submit">
          <i class="fas fa-save" aria-hidden="true"></i> Guardar sección
        </button>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-dark">Cancelar</a>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.addEventListener('change', function (event) {
        const input = event.target.closest('[data-section-preview]');
        if (!input) return;
        const file = input.files && input.files[0];
        const preview = document.querySelector(`[data-section-image="${input.dataset.sectionPreview}"]`);
        if (file && preview) preview.src = URL.createObjectURL(file);
      });
      document.addEventListener('change', function (event) {
        const select = event.target.closest('[data-icon-choice]');
        if (!select) return;
        const preview = select.parentElement.querySelector('[data-icon-preview]');
        if (preview) preview.className = `fas ${select.value}`;
      });

      const addPublicationItem = document.getElementById('addPublicationItem');
      const publicationItems = document.getElementById('publicationItems');
      const publicationCount = document.getElementById('publicationCount');
      const publicationTemplate = document.getElementById('publicationItemTemplate');
      const publicationModal = document.getElementById('publicationAddModal');
      const publicationModalFields = publicationModal?.querySelector('.publications-admin-modal-fields');
      const savePublicationItem = document.getElementById('savePublicationItem');
      const publicationForm = addPublicationItem?.closest('form');

      if (addPublicationItem && publicationItems && publicationCount && publicationTemplate && publicationModal && publicationModalFields && savePublicationItem && publicationForm) {
        document.body.append(publicationModal);
        let previouslyFocusedPublicationElement = null;
        let previousPublicationBodyOverflow = '';

        const getPublicationIndexes = function () {
          return new Set(Array.from(publicationItems.querySelectorAll('[data-publication-item]'), item => Number(item.dataset.publicationIndex)));
        };

        const updatePublicationSummary = function (item) {
          const index = item.dataset.publicationIndex;
          const read = field => item.querySelector(`[name="slide_${index}_${field}"]`)?.value.trim() || '';
          const title = item.querySelector('[data-publication-summary-title]');
          const meta = item.querySelector('[data-publication-summary-meta]');
          if (title) title.textContent = read('title') || 'Nueva publicación';
          if (meta) {
            const start = read('starts_at');
            const end = read('ends_at');
            meta.textContent = start || end
              ? `${start || 'Desde siempre'} — ${end || 'Sin fecha de fin'}`
              : 'Siempre visible';
          }
        };

        const updatePublicationCount = function () {
          const indexes = Array.from(getPublicationIndexes());
          publicationCount.value = String(indexes.length ? Math.max(...indexes) : 0);
          const activeCount = indexes.length;
          addPublicationItem.disabled = activeCount >= 30;
          const visibleCount = document.getElementById('publicationVisibleCount');
          if (visibleCount) visibleCount.textContent = String(activeCount);
        };

        const closePublicationModal = function () {
          publicationModal.classList.remove('open');
          publicationModal.setAttribute('aria-hidden', 'true');
          publicationModal.querySelectorAll('[data-modal-publication-field]').forEach(field => {
            field.value = '';
            field.setCustomValidity('');
          });
          const modalImage = publicationModal.querySelector('[data-modal-publication-image]');
          if (modalImage) modalImage.value = '';
          publicationModalFields.disabled = true;
          document.body.style.overflow = previousPublicationBodyOverflow;
          const focusTarget = previouslyFocusedPublicationElement?.isConnected
            ? previouslyFocusedPublicationElement
            : addPublicationItem;
          focusTarget.focus();
          previouslyFocusedPublicationElement = null;
        };

        addPublicationItem.addEventListener('click', function () {
          if (getPublicationIndexes().size >= 30) return;
          previouslyFocusedPublicationElement = document.activeElement;
          previousPublicationBodyOverflow = document.body.style.overflow;
          document.body.style.overflow = 'hidden';
          publicationModal.classList.add('open');
          publicationModal.setAttribute('aria-hidden', 'false');
          publicationModalFields.disabled = false;
          publicationModal.querySelector('[data-modal-publication-field="title"]').focus();
        });

        publicationModal.querySelectorAll('[data-close-publication-modal]').forEach(button => {
          button.addEventListener('click', closePublicationModal);
        });
        document.addEventListener('keydown', function (event) {
          if (!publicationModal.classList.contains('open')) return;
          if (event.key === 'Escape') {
            closePublicationModal();
            return;
          }
          if (event.key !== 'Tab') return;

          const focusable = Array.from(publicationModal.querySelectorAll('button:not(:disabled), input:not(:disabled), textarea:not(:disabled)'))
            .filter(element => element.getClientRects().length > 0);
          const first = focusable[0];
          const last = focusable[focusable.length - 1];
          if (!first || !last) return;
          if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
          } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
          }
        });

        savePublicationItem.addEventListener('click', function () {
          const title = publicationModal.querySelector('[data-modal-publication-field="title"]');
          if (!title.reportValidity()) return;

          const startsAt = publicationModal.querySelector('[data-modal-publication-field="starts_at"]').value;
          const endsAt = publicationModal.querySelector('[data-modal-publication-field="ends_at"]').value;
          if (startsAt && endsAt && endsAt < startsAt) {
            const endField = publicationModal.querySelector('[data-modal-publication-field="ends_at"]');
            endField.setCustomValidity('La fecha de fin debe ser igual o posterior a la fecha de inicio.');
            endField.reportValidity();
            endField.setCustomValidity('');
            return;
          }

          const used = getPublicationIndexes();
          let index = 1;
          while (used.has(index) && index <= 30) index++;
          if (index > 30) return;

          const itemTemplate = document.createElement('template');
          itemTemplate.innerHTML = publicationTemplate.innerHTML
            .replaceAll('__INDEX__', String(index))
            .replaceAll('__PADDED_INDEX__', String(index).padStart(2, '0'));
          const item = itemTemplate.content.firstElementChild;
          item.querySelectorAll('[data-publication-field]').forEach(field => {
            const key = field.dataset.publicationField;
            field.name = `slide_${index}_${key}`;
            field.value = publicationModal.querySelector(`[data-modal-publication-field="${key}"]`).value;
          });
          const iconSelect = item.querySelector('[data-publication-icon-select]');
          const iconPreview = item.querySelector('[data-publication-icon-preview]');
          if (iconSelect && iconPreview) iconPreview.className = `fas ${iconSelect.value}`;

          const imageField = item.querySelector('[data-publication-image]');
          imageField.name = `media_slide_${index}_image`;
          imageField.dataset.sectionPreview = `slide_${index}_image`;
          const modalImage = publicationModal.querySelector('[data-modal-publication-image]');
          const freshModalImage = modalImage.cloneNode(false);
          freshModalImage.value = '';
          modalImage.replaceWith(freshModalImage);
          modalImage.dataset.sectionPreview = `slide_${index}_image`;
          imageField.replaceWith(modalImage);
          const movedImage = item.querySelector(`[data-section-preview="slide_${index}_image"]`);
          const preview = item.querySelector('.publications-admin-item-preview');
          preview.dataset.sectionImage = `slide_${index}_image`;
          if (movedImage.files?.[0]) preview.src = URL.createObjectURL(movedImage.files[0]);

          publicationForm.querySelectorAll('[name="publication_deleted[]"]').forEach(deleted => {
            if (Number(deleted.value) === index) deleted.remove();
          });
          publicationItems.append(item);
          updatePublicationSummary(item);
          updatePublicationCount();
          closePublicationModal();
        });

        publicationItems.addEventListener('click', function (event) {
          const removeButton = event.target.closest('[data-remove-publication]');
          if (!removeButton) return;
          event.preventDefault();
          event.stopPropagation();
          const item = removeButton.closest('[data-publication-item]');
          const index = Number(item.dataset.publicationIndex);
          const deleted = document.createElement('input');
          deleted.type = 'hidden';
          deleted.name = 'publication_deleted[]';
          deleted.value = String(index);
          publicationForm.append(deleted);
          item.remove();
          updatePublicationCount();
          addPublicationItem.focus();
        });

        const refreshPublicationSummary = function (event) {
          const field = event.target.closest('[name^="slide_"]');
          const item = field?.closest('[data-publication-item]');
          if (item) updatePublicationSummary(item);
        };
        publicationItems.addEventListener('input', refreshPublicationSummary);
        publicationItems.addEventListener('change', refreshPublicationSummary);
        publicationItems.querySelectorAll('[data-publication-item]').forEach(updatePublicationSummary);
        updatePublicationCount();
      }

      const addNewsItem = document.getElementById('addNewsItem');
      const newsItems = document.getElementById('newsItems');
      const newsCount = document.getElementById('newsCount');
      const newsTemplate = document.getElementById('newsItemTemplate');
      const newsModal = document.getElementById('newsAddModal');
      const newsModalFields = newsModal?.querySelector('.news-admin-modal-fields');
      const saveNewsItem = document.getElementById('saveNewsItem');
      const newsForm = addNewsItem?.closest('form');
      if (!addNewsItem || !newsItems || !newsCount || !newsTemplate || !newsModal || !newsModalFields || !saveNewsItem || !newsForm) return;

      document.body.append(newsModal);
      let previouslyFocusedElement = null;
      let previousBodyOverflow = '';

      const getUsedIndexes = function () {
        return new Set(Array.from(newsItems.querySelectorAll('[data-news-item]'), item => Number(item.dataset.newsIndex)));
      };

      const updateNewsSummary = function (item) {
        const index = item.dataset.newsIndex;
        const read = field => item.querySelector(`[name="news_${index}_${field}"]`)?.value.trim() || '';
        const title = item.querySelector('[data-news-summary-title]');
        const meta = item.querySelector('[data-news-summary-meta]');
        if (title) title.textContent = read('title') || 'Nueva novedad';
        if (meta) {
          const category = read('tag') || 'Sin categoría';
          const start = read('starts_at');
          const end = read('ends_at');
          const period = start || end
            ? `${start || 'Desde siempre'}${end ? ` — ${end}` : ''}`
            : 'Siempre visible';
          meta.textContent = `${category} · ${period}`;
        }
      };

      const updateNewsCount = function () {
        const indexes = Array.from(getUsedIndexes());
        newsCount.value = String(indexes.length ? Math.max(...indexes) : 0);
        const activeCount = getUsedIndexes().size;
        addNewsItem.disabled = activeCount >= 30;
        const visibleCount = document.getElementById('newsVisibleCount');
        if (visibleCount) visibleCount.textContent = String(activeCount);
      };

      const closeModal = function () {
        newsModal.classList.remove('open');
        newsModal.setAttribute('aria-hidden', 'true');
        newsModal.querySelectorAll('[data-modal-news-field]').forEach(field => {
          field.value = field.tagName === 'SELECT' ? field.options[0].value : '';
        });
        const modalIconPreview = newsModal.querySelector('[data-icon-preview]');
        if (modalIconPreview) modalIconPreview.className = `fas ${newsModal.querySelector('[data-modal-news-field="icon"]').value}`;
        const image = newsModal.querySelector('[data-modal-news-image]');
        if (image) image.value = '';
        newsModalFields.disabled = true;
        document.body.style.overflow = previousBodyOverflow;
        const focusTarget = previouslyFocusedElement?.isConnected ? previouslyFocusedElement : addNewsItem;
        focusTarget.focus();
        previouslyFocusedElement = null;
      };

      addNewsItem.addEventListener('click', function () {
        if (getUsedIndexes().size >= 30) return;
        previouslyFocusedElement = document.activeElement;
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        newsModal.classList.add('open');
        newsModal.setAttribute('aria-hidden', 'false');
        newsModalFields.disabled = false;
        newsModal.querySelector('[data-modal-news-field="title"]').focus();
      });

      newsModal.querySelectorAll('[data-close-news-modal]').forEach(button => button.addEventListener('click', closeModal));
      document.addEventListener('keydown', function (event) {
        if (!newsModal.classList.contains('open')) return;
        if (event.key === 'Escape') {
          closeModal();
          return;
        }
        if (event.key !== 'Tab') return;

        const focusable = Array.from(newsModal.querySelectorAll('button:not(:disabled), input:not(:disabled), textarea:not(:disabled)'))
          .filter(element => element.getClientRects().length > 0);
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (!first || !last) return;
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      });

      saveNewsItem.addEventListener('click', function () {
        const title = newsModal.querySelector('[data-modal-news-field="title"]');
        if (!title.reportValidity()) return;

        const startsAt = newsModal.querySelector('[data-modal-news-field="starts_at"]').value;
        const endsAt = newsModal.querySelector('[data-modal-news-field="ends_at"]').value;
        if (startsAt && endsAt && endsAt < startsAt) {
          newsModal.querySelector('[data-modal-news-field="ends_at"]').setCustomValidity('La fecha de fin debe ser igual o posterior a la fecha de inicio.');
          newsModal.querySelector('[data-modal-news-field="ends_at"]').reportValidity();
          newsModal.querySelector('[data-modal-news-field="ends_at"]').setCustomValidity('');
          return;
        }

        const used = getUsedIndexes();
        let index = 1;
        while (used.has(index) && index <= 30) index++;
        if (index > 30) return;

        const itemTemplate = document.createElement('template');
        itemTemplate.innerHTML = newsTemplate.innerHTML
          .replaceAll('__INDEX__', String(index))
          .replaceAll('__PADDED_INDEX__', String(index).padStart(2, '0'));
        const item = itemTemplate.content.firstElementChild;
        item.querySelectorAll('[data-news-field]').forEach(field => {
          const key = field.dataset.newsField;
          field.name = `news_${index}_${key}`;
          field.value = newsModal.querySelector(`[data-modal-news-field="${key}"]`).value;
        });
        const iconSelect = item.querySelector('[data-icon-choice]');
        const iconPreview = item.querySelector('[data-icon-preview]');
        if (iconSelect && iconPreview) iconPreview.className = `fas ${iconSelect.value}`;

        const imageField = item.querySelector('[data-news-image]');
        imageField.name = `media_news_${index}_image`;
        imageField.dataset.sectionPreview = `news_${index}_image`;
        const modalImage = newsModal.querySelector('[data-modal-news-image]');
        const freshModalImage = modalImage.cloneNode(false);
        freshModalImage.value = '';
        modalImage.replaceWith(freshModalImage);
        modalImage.dataset.sectionPreview = `news_${index}_image`;
        imageField.replaceWith(modalImage);
        const movedImage = item.querySelector(`[data-section-preview="news_${index}_image"]`);
        const preview = item.querySelector('.news-admin-image-preview');
        preview.dataset.sectionImage = `news_${index}_image`;
        if (movedImage.files?.[0]) preview.src = URL.createObjectURL(movedImage.files[0]);

        const clearOldImage = document.createElement('input');
        clearOldImage.type = 'hidden';
        clearOldImage.name = `remove_news_${index}_image`;
        clearOldImage.value = '1';
        item.append(clearOldImage);

        const previouslyDeleted = newsForm.querySelector(`[name="news_deleted[]"][value="${index}"]`);
        previouslyDeleted?.remove();
        newsItems.append(item);
        updateNewsSummary(item);
        updateNewsCount();
        closeModal();
      });

      newsItems.addEventListener('click', function (event) {
        const removeButton = event.target.closest('[data-remove-news]');
        if (!removeButton) return;
        event.preventDefault();
        event.stopPropagation();
        const item = removeButton.closest('[data-news-item]');
        const index = Number(item.dataset.newsIndex);
        const deleted = document.createElement('input');
        deleted.type = 'hidden';
        deleted.name = 'news_deleted[]';
        deleted.value = String(index);
        newsForm.append(deleted);
        item.remove();
        updateNewsCount();
      });

      const refreshSummary = function (event) {
        const field = event.target.closest('[name^="news_"]');
        const item = field?.closest('[data-news-item]');
        if (item) updateNewsSummary(item);
      };
      newsItems.addEventListener('input', refreshSummary);
      newsItems.addEventListener('change', refreshSummary);
      newsItems.querySelectorAll('[data-news-item]').forEach(updateNewsSummary);
      updateNewsCount();
    });
  </script>
@endpush
