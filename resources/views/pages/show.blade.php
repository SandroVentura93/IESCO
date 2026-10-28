@extends('layouts.app')

@section('full_width')
@endsection

@section('content')
  <section class="managed-page">
    <article class="managed-page-card">
      <a class="managed-page-back" href="{{ route('home') }}#{{ $page->section === 'institucional' ? 'quienes-somos' : $page->section }}">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al sitio
      </a>
      <header class="managed-page-header">
        <span class="managed-page-section">{{ $page->section_label }}</span>
        <h1>{{ $page->title }}</h1>
        <p>Publicado {{ $page->updated_at->translatedFormat('d \d\e F \d\e Y') }}</p>
      </header>
      @if($page->featured_image)
        <img class="managed-page-featured-image"
             src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->featured_image) }}"
             alt="{{ $page->title }}" />
      @endif
      <div class="managed-page-content">{!! $safeContent !!}</div>
    </article>
  </section>
@endsection

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/home-premium.css') }}?v={{ filemtime(public_path('css/home-premium.css')) }}" />
@endpush

@section('page_title', $page->title)
