@extends('layouts.app_admin')

@section('title', 'Detalles del Trabajador')

@section('content')
<div class="card mt-4">
  <div class="card-header">Detalles del Trabajador</div>
  <div class="card-body">
    <p><strong>Nombre:</strong> {{ $trabajador->nombre }}</p>
    <p><strong>Apellidos:</strong> {{ $trabajador->apellido_paterno }} {{ $trabajador->apellido_materno }}</p>
    <p><strong>Correo:</strong> {{ $trabajador->email }}</p>
    <p><strong>Fecha de Nacimiento:</strong> {{ $trabajador->fecha_nacimiento }}</p>
    <p><strong>Rol:</strong> {{ $trabajador->rol }}</p>
    <a href="{{ route('trabajadores') }}" class="btn btn-secondary">Volver</a>
  </div>
</div>
@endsection
