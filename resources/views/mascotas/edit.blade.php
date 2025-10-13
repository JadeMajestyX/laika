@extends('layouts.app_admin')

@section('title', 'Editar Mascota')

@section('content')
<div class="container mt-4">
    <h2>Editar Mascota</h2>

    <form action="{{ route('mascotas.update', $mascota->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $mascota->nombre) }}" required>
        </div>

        <div class="mb-3">
            <label for="especie" class="form-label">Especie:</label>
            <input type="text" name="especie" class="form-control" value="{{ old('especie', $mascota->especie) }}" required>
        </div>

        <div class="mb-3">
            <label for="raza" class="form-label">Raza:</label>
            <input type="text" name="raza" class="form-control" value="{{ old('raza', $mascota->raza) }}">
        </div>

        <div class="mb-3">
            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
            <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', $mascota->fecha_nacimiento) }}">
        </div>

        <div class="mb-3">
            <label for="peso" class="form-label">Peso (kg):</label>
            <input type="number" name="peso" class="form-control" value="{{ old('peso', $mascota->peso) }}">
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('mascotas') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
