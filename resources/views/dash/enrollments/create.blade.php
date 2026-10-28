@extends('layouts.admin')

@php
  $adminSection = 'enrollments';
  $formTitle = 'Nueva matrícula';
  $formDescription = 'Registra el año lectivo, grado, sección y datos de contacto del apoderado.';
  $formAction = route('admin.enrollments.store');
  $submitLabel = 'Crear ficha de matrícula';
@endphp

@section('admin_content')
  @include('dash.enrollments._form')
@endsection
