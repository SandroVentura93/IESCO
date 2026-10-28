@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/login-premium.css') }}?v={{ filemtime(public_path('css/login-premium.css')) }}">
@endpush

@section('content')
  <section class="section login-viewport login-page" aria-label="Acceso al portal institucional">
    <div class="login-card-wrap">
      <div class="bento-card login-card-surface fade-in-up">
        <aside class="login-side-panel">
          <div class="login-side-inner">
            <div class="login-brand">
              <div class="login-logo" aria-hidden="true">
                <img src="{{ asset('images/LOGO.png') }}" alt="" />
              </div>
              <div class="login-side-copy">
                <span class="login-side-kicker"><i class="fas fa-shield-alt" aria-hidden="true"></i> Portal institucional</span>
                <h1 class="login-side-title">Tu comunidad,<br>un solo espacio.</h1>
                <p class="login-side-description">{{ config('school.name') }}</p>
              </div>
            </div>

            <ul class="login-hero-list">
              <li><i class="fas fa-book-open" aria-hidden="true"></i><span>Materiales y recursos de aprendizaje</span></li>
              <li><i class="fas fa-users" aria-hidden="true"></i><span>Un espacio para toda la comunidad educativa</span></li>
              <li><i class="fas fa-lightbulb" aria-hidden="true"></i><span>Proyectos, actividades y novedades</span></li>
            </ul>
            <p class="login-side-motto">{{ config('school.motto') }}</p>
          </div>
        </aside>

        <div class="login-form-panel" aria-labelledby="login-heading">
          <div class="login-form-inner">
            @if($errors->any())
              <div class="mb-4 alert-error login-error" role="alert" aria-live="assertive">
                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                <strong>Error:</strong> {{ $errors->first() }}
              </div>
            @endif

            <header class="login-form-heading">
              <span class="login-form-eyebrow">Bienvenido de nuevo</span>
              <h2 id="login-heading" class="login-title">Iniciar sesión</h2>
              <p>Ingresa con tus credenciales para continuar.</p>
            </header>

            <form method="POST" action="{{ url('/login') }}" aria-label="Formulario de inicio de sesión">
              @csrf

              <div class="login-field">
                <label for="loginEmail">Correo electrónico</label>
                <div class="login-input-wrap">
                  <i class="fas fa-envelope" aria-hidden="true"></i>
                  <input id="loginEmail" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="input" placeholder="nombre@institucion.edu" />
                </div>
              </div>

              <div class="login-field">
                <label for="loginPassword">Contraseña</label>
                <div class="login-input-wrap input-with-toggle">
                  <i class="fas fa-lock" aria-hidden="true"></i>
                  <input id="loginPassword" type="password" name="password" required autocomplete="current-password" class="input" placeholder="Ingresa tu contraseña" />
                  <button type="button" id="togglePass" aria-label="Mostrar contraseña" class="login-password-toggle" title="Mostrar contraseña">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                  </button>
                </div>
              </div>

              <div class="login-options">
                <label class="login-remember">
                  <input type="checkbox" name="remember" value="1" /> <span>Recordarme</span>
                </label>
              </div>

              <div class="form-actions">
                <button class="btn btn-primary login-submit" type="submit">
                  <span>Entrar al portal</span>
                  <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </button>
              </div>
            </form>
            <p class="login-form-note"><i class="fas fa-lock" aria-hidden="true"></i> Tu acceso está protegido y es de uso institucional.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

@push('scripts')
<script>
  // toggle password visibility
  (function(){
    document.addEventListener('DOMContentLoaded', function(){
      const btn = document.getElementById('togglePass');
      const inp = document.getElementById('loginPassword');
      if(!btn || !inp) return;
      btn.addEventListener('click', function(){
        const isHidden = inp.type === 'password';
        const icon = btn.querySelector('i');
        inp.type = isHidden ? 'text' : 'password';
        btn.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
        btn.setAttribute('title', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
        if (icon) icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
      });
    });
  })();
</script>
@endpush
@endsection
