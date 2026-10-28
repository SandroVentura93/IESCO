@extends('layouts.admin')

@section('admin_content')
  <div class="admin-card">
    <div class="section-header admin-page-header">
      <h2 class="section-title">Configuración del sitio</h2>
      <div class="section-divider"></div>
      <p>Ajustes globales: nombre, colores, redes sociales, etc.</p>
    </div>

    <div class="admin-panel">
      @if(session('success'))<div class="admin-alert-success">{{ session('success') }}</div>@endif
      <form method="POST" action="{{ route('admin.settings.store') }}" class="admin-form-stack" enctype="multipart/form-data">
        @csrf
        <h3>General</h3>
        <label>Nombre del sitio</label>
        <input type="text" name="site_name" value="{{ $settings['site_name'] ?? '' }}" />
        <label>Nombre corto</label>
        <input type="text" name="site_short_name" value="{{ $settings['site_short_name'] ?? '' }}" />
        <label>Lema</label>
        <input type="text" name="site_motto" value="{{ $settings['site_motto'] ?? '' }}" />
        <label>Año</label>
        <input type="number" name="site_year" value="{{ $settings['site_year'] ?? '' }}" />
        <label>Director</label>
        <input type="text" name="site_director" value="{{ $settings['site_director'] ?? '' }}" />

        <label>Color primario (CSS)</label>
        <input type="text" name="color_primary" value="{{ $settings['color_primary'] ?? '' }}" />
        <label>Color secundario (CSS)</label>
        <input type="text" name="color_secondary" value="{{ $settings['color_secondary'] ?? '' }}" />

        <label>Facebook (URL)</label>
        <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" />
        <label>Instagram (URL)</label>
        <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" />
        <label>YouTube (URL)</label>
        <input type="url" name="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}" />
        <label>Twitter/X (URL)</label>
        <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}" />
        
        <h3>Inicio (sección #inicio)</h3>
        <label>Contenido (HTML)</label>
        <textarea name="inicio_content" rows="6">{{ $settings['inicio_content'] ?? '' }}</textarea>
        <label>Video (URL)</label>
        <input type="url" name="inicio_video" value="{{ $settings['inicio_video'] ?? '' }}" />
        <div id="preview_inicio_video_container" style="margin-top:.6rem;"></div>
        <label>Imagen de fondo (subir o usar URL)</label>
        <input type="file" name="inicio_image" data-preview="preview_inicio_image" accept="image/*" />
        @if(!empty($settings['inicio_image']))
          <img id="preview_inicio_image" src="{{ $settings['inicio_image'] }}" alt="Preview inicio" style="max-width:320px;margin-top:.5rem;border-radius:8px;" />
        @else
          <img id="preview_inicio_image" src="" alt="Preview inicio" style="display:none;max-width:320px;margin-top:.5rem;border-radius:8px;" />
        @endif

        <h3>¿Quiénes somos?</h3>
        <label>Contenido (HTML)</label>
        <textarea name="quienes_content" rows="6">{{ $settings['quienes_content'] ?? '' }}</textarea>
        <label>Imagen principal</label>
        <input type="file" name="quienes_image" data-preview="preview_quienes_image" accept="image/*" />
        <input type="text" name="quienes_image_alt" placeholder="Texto alternativo para la imagen" value="{{ $settings['quienes_image_alt'] ?? '' }}" />
        @if(!empty($settings['quienes_image']))
          <img id="preview_quienes_image" src="{{ $settings['quienes_image'] }}" alt="Preview quienes" style="max-width:320px;margin-top:.5rem;border-radius:8px;" />
        @else
          <img id="preview_quienes_image" src="" alt="Preview quienes" style="display:none;max-width:320px;margin-top:.5rem;border-radius:8px;" />
        @endif

        <h3>Proyecto IJENT</h3>
        <label>Contenido (HTML)</label>
        <textarea name="proyecto_content" rows="4">{{ $settings['proyecto_content'] ?? '' }}</textarea>
        <label>Imagen destacada</label>
        <input type="file" name="proyecto_image" data-preview="preview_proyecto_image" accept="image/*" />
        @if(!empty($settings['proyecto_image']))
          <img id="preview_proyecto_image" src="{{ $settings['proyecto_image'] }}" alt="Preview proyecto" style="max-width:320px;margin-top:.5rem;border-radius:8px;" />
        @else
          <img id="preview_proyecto_image" src="" alt="Preview proyecto" style="display:none;max-width:320px;margin-top:.5rem;border-radius:8px;" />
        @endif

        <h3>Novedades / Publicaciones (textos por defecto)</h3>
        <label>Novedades (HTML)</label>
        <textarea name="novedades_content" rows="4">{{ $settings['novedades_content'] ?? '' }}</textarea>
        <label>Publicaciones (HTML)</label>
        <textarea name="publicaciones_content" rows="4">{{ $settings['publicaciones_content'] ?? '' }}</textarea>

        <div class="admin-actions-row"><button class="btn btn-primary" type="submit">Guardar configuración</button></div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // Small helper: show preview when selecting an image
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('input[type=file]').forEach(function(inp){
      inp.addEventListener('change', function(){
        const p = this.getAttribute('data-preview');
        if(!p) return; const img = document.getElementById(p);
        const f = this.files && this.files[0]; if(!f) return; const url = URL.createObjectURL(f); if(img){ img.src = url; }
      });
    });
    // Live preview for inicio_video URL
    const videoInput = document.querySelector('input[name="inicio_video"]');
    const previewContainer = document.getElementById('preview_inicio_video_container');
    function renderVideoPreview(url){
      if(!previewContainer) return;
      url = (url||'').trim();
      if(!url){ previewContainer.innerHTML = ''; return; }
      // detect youtube, youtu.be, vimeo, or direct mp4
      const isYouTube = /(?:youtube\.com|youtu\.be)/i.test(url);
      const isVimeo = /vimeo\.com|player\.vimeo\.com/i.test(url);
      const isMp4 = /\.(mp4)(?:\?|$)/i.test(url);
      if(isYouTube){
        let vid = null;
        try{ const q = new URL(url).searchParams; vid = q.get('v'); }catch(e){}
        if(!vid){ // youtu.be
          const parts = url.split('/'); vid = parts.pop();
        }
        if(vid){
          // autoplay muted to allow autoplay in modern browsers; playsinline for mobile
          previewContainer.innerHTML = '<div class="preview-wrapper" style="position:relative;padding-top:56.25%;overflow:hidden;border-radius:8px;box-shadow:0 6px 18px rgba(7,20,40,0.06);"><iframe src="https://www.youtube.com/embed/'+vid+'?autoplay=1&mute=1&playsinline=1" frameborder="0" allow="autoplay; encrypted-media; fullscreen; picture-in-picture" allowfullscreen style="position:absolute;inset:0;width:100%;height:100%;"></iframe><button class="preview-fullscreen-btn" title="Pantalla completa" style="position:absolute;top:8px;right:8px;z-index:20;padding:8px;border-radius:6px;background:rgba(0,0,0,.55);color:#fff;border:0;cursor:pointer;">⤢</button></div>';
          // attach fullscreen handler
          const btn = previewContainer.querySelector('.preview-fullscreen-btn');
          if(btn){ btn.addEventListener('click', function(){ const wrapper = previewContainer.querySelector('.preview-wrapper'); const el = wrapper.querySelector('iframe,video') || wrapper; if(el.requestFullscreen){ el.requestFullscreen().catch(()=>{}); } else if(el.webkitEnterFullscreen){ try{ el.webkitEnterFullscreen(); }catch(e){} } }); }
          return;
        }
      }
      if(isVimeo){
        // extract numeric id
        let m = url.match(/(\d+)/);
        const vid = m ? m[1] : null;
        if(vid){
          previewContainer.innerHTML = '<div class="preview-wrapper" style="position:relative;padding-top:56.25%;overflow:hidden;border-radius:8px;box-shadow:0 6px 18px rgba(7,20,40,0.06);"><iframe src="https://player.vimeo.com/video/'+vid+'?autoplay=1&muted=1&playsinline=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute;inset:0;width:100%;height:100%;"></iframe><button class="preview-fullscreen-btn" title="Pantalla completa" style="position:absolute;top:8px;right:8px;z-index:20;padding:8px;border-radius:6px;background:rgba(0,0,0,.55);color:#fff;border:0;cursor:pointer;">⤢</button></div>';
          const btnV = previewContainer.querySelector('.preview-fullscreen-btn');
          if(btnV){ btnV.addEventListener('click', function(){ const wrapper = previewContainer.querySelector('.preview-wrapper'); const el = wrapper.querySelector('iframe,video') || wrapper; if(el.requestFullscreen){ el.requestFullscreen().catch(()=>{}); } else if(el.webkitEnterFullscreen){ try{ el.webkitEnterFullscreen(); }catch(e){} } }); }
          return;
        }
      }
      if(isMp4){
        previewContainer.innerHTML = '<div class="preview-wrapper" style="position:relative;"> <video class="preview-video" autoplay muted playsinline style="width:100%;border-radius:8px;box-shadow:0 6px 18px rgba(7,20,40,0.06);"><source src="'+url+'" type="video/mp4">Your browser does not support the video tag.</video><button class="preview-fullscreen-btn" title="Pantalla completa" style="position:absolute;top:8px;right:8px;z-index:20;padding:8px;border-radius:6px;background:rgba(0,0,0,.55);color:#fff;border:0;cursor:pointer;">⤢</button></div>';
        const fbtn = previewContainer.querySelector('.preview-fullscreen-btn');
        const v = previewContainer.querySelector('.preview-video');
        if(fbtn){ fbtn.addEventListener('click', function(){ const el = v || previewContainer.querySelector('.preview-wrapper'); if(el.requestFullscreen){ el.requestFullscreen().catch(()=>{}); } else if(el.webkitEnterFullscreen){ try{ el.webkitEnterFullscreen(); }catch(e){} } }); }
        // ensure video plays (some browsers require .play() after DOM changes)
        if(v){ v.play().catch(()=>{}); }
        return;
      }
      // default: show link
      previewContainer.innerHTML = '<a href="'+url+'" target="_blank" rel="noopener">Ver video</a>';
    }
    if(videoInput){
        videoInput.addEventListener('input', function(e){ renderVideoPreview(e.target.value); });
        // initial render
        renderVideoPreview(videoInput.value);
    }
  });
</script>
@endpush
