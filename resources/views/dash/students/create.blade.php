@extends('layouts.admin')

@php
  $adminSection = 'students';
  $formTitle = 'Registrar alumno';
  $formDescription = 'Completa la información básica para incorporar al alumno al directorio.';
  $formAction = route('admin.students.store');
  $submitLabel = 'Registrar alumno';
@endphp

@section('admin_content')
  @include('dash.students._form')
@endsection
