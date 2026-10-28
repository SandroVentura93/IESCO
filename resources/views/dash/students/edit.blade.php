@extends('layouts.admin')

@php
  $adminSection = 'students';
  $formTitle = 'Editar alumno';
  $formDescription = 'Actualiza la información del alumno y guarda los cambios.';
  $formAction = route('admin.students.update', $s->id);
  $submitLabel = 'Guardar cambios';
@endphp

@section('admin_content')
  @include('dash.students._form', ['student' => $s, 'editing' => true])
@endsection
