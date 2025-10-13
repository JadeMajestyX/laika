@extends('layouts.app_admin')

@section('title', 'Editar Usuario')

@section('content')
<div class="container mt-4">
    <h2>Editar Usuario</h2>

    <form action="{{ route('usuarios.update', $cliente->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cliente->nombre) }}" required>
        </div>

        <div class="mb-3">
            <label for="apellido_paterno" class="form-label">Apellido Paterno:</label>
            <input type="text" name="apellido_paterno" class="form-control" value="{{ old('apellido_paterno', $cliente->apellido_paterno) }}">
        </div>

        <div class="mb-3">
            <label for="apellido_materno" class="form-label">Apellido Materno:</label>
            <input type="text" name="apellido_materno" class="form-control" value="{{ old('apellido_materno', $cliente->apellido_materno) }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo:</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $cliente->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="rol" class="form-label">Rol:</label>
            <select name="rol" class="form-select">
                <option value="U" {{ $cliente->rol == 'U' ? 'selected' : '' }}>Usuario</option>
                <option value="A" {{ $cliente->rol == 'A' ? 'selected' : '' }}>Administrador</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('usuarios') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
