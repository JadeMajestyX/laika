@extends('layouts.app_admin')

@section('title', 'Editar Trabajador')

@section('content')
<div class="card mt-4">
  <div class="card-header">Editar Trabajador</div>
  <div class="card-body">
    <form method="POST" action="{{ route('trabajadores.update', $trabajador->id) }}">
      @csrf
      @method('PUT')
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ $trabajador->nombre }}">
      </div>
      <div class="mb-3">
        <label class="form-label">Apellido Paterno</label>
        <input type="text" name="apellido_paterno" class="form-control" value="{{ $trabajador->apellido_paterno }}">
      </div>
      <div class="mb-3">
        <label class="form-label">Apellido Materno</label>
        <input type="text" name="apellido_materno" class="form-control" value="{{ $trabajador->apellido_materno }}">
      </div>
      <div class="mb-3">
        <label class="form-label">Correo</label>
        <input type="email" name="email" class="form-control" value="{{ $trabajador->email }}">
      </div>
      <div class="mb-3">
        <label class="form-label">Fecha de Nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control" value="{{ $trabajador->fecha_nacimiento }}">
      </div>
      <button type="submit" class="btn btn-success">Guardar Cambios</button>
      <a href="{{ route('trabajadores') }}" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
@endsection
