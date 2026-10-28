@extends('layouts.admin')

@php
  $adminSection = 'enrollments';
  $formTitle = 'Editar matrícula';
  $formDescription = 'Revisa y actualiza los datos de esta matrícula. El código de ficha se conserva.';
  $formAction = route('admin.enrollments.update', $enrollment->id);
  $submitLabel = 'Guardar cambios';
  $students = collect([$enrollment->student]);
  $academicYear = $enrollment->academic_year;
  $selectedStudentId = $enrollment->student_id;
@endphp

@section('admin_content')
  @include('dash.enrollments._form', ['editing' => true])
@endsection
