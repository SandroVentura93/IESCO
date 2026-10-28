@if(isset($items) && $items->isNotEmpty())
  <div class="section-managed-pages" aria-label="Contenido publicado en esta sección">
    <div class="section-managed-page-grid">
      @foreach($items as $item)
        <article class="section-managed-page-card">
          @if($item->featured_image)
            <a class="section-managed-page-image" href="{{ route('pages.show', $item->slug) }}" tabindex="-1" aria-hidden="true">
              <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->featured_image) }}"
                   alt="" loading="lazy" />
            </a>
          @else
            <div class="section-managed-page-icon" aria-hidden="true"><i class="fas fa-file-lines"></i></div>
          @endif
          <div class="section-managed-page-body">
            <span class="section-managed-page-label">{{ $item->section_label }}</span>
            <h3><a href="{{ route('pages.show', $item->slug) }}">{{ $item->title }}</a></h3>
            <p>{{ \Illuminate\Support\Str::limit(trim(strip_tags($item->content ?? '')), 150, '…') ?: 'Lee el contenido completo de esta publicación institucional.' }}</p>
            <a class="section-managed-page-link" href="{{ route('pages.show', $item->slug) }}">
              Leer contenido <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </article>
      @endforeach
    </div>
  </div>
@endif
