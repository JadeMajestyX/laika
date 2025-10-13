@extends('layouts.app_admin')

@section('title', 'Registrar Administrador')

@section('content')
<div class="card mt-4">
  <div class="card-header">Registrar Administrador</div>
  <div class="card-body">
    <form method="POST" action="{{ route('trabajadores.store') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Apellido Paterno</label>
        <input type="text" name="apellido_paterno" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Apellido Materno</label>
        <input type="text" name="apellido_materno" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Correo</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Fecha de Nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirmar Contraseña</label>
        <input type="password" name="password_confirmation" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success">Registrar</button>
      <a href="{{ route('trabajadores') }}" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
@endsection
