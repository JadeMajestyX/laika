@extends('layouts.app_admin')

{{-- Título de la página --}}
@section('title', 'Usuarios')

{{-- Aside específico para esta vista --}}
@section('aside')
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white active" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a></li>
@endsection

@section('header-title', 'Usuarios')

@section('content')

<!-- Filtros -->
<div class="card shadow-sm mt-4 mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <form action="{{ route('usuarios') }}" method="GET" class="d-flex gap-2 align-items-center">
            <div>
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o apellido" value="{{ request('search') }}">
            </div>
            <div>
                <select name="rol" class="form-select">
                    <option value="">Todos los roles</option>
                    <option value="A" {{ request('rol') == 'A' ? 'selected' : '' }}>Administrador</option>
                    <option value="V" {{ request('rol') == 'V' ? 'selected' : '' }}>Veterinario</option>
                    <option value="G" {{ request('rol') == 'G' ? 'selected' : '' }}>Groomer</option>
                    <option value="R" {{ request('rol') == 'R' ? 'selected' : '' }}>Recepcionista</option>
                    <option value="U" {{ request('rol') == 'U' ? 'selected' : '' }}>Usuario</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill me-1"></i> Filtrar</button>
            </div>
        </form>
        <button class="add-btn"><i class="bi bi-plus-lg"></i></button>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th> <!-- Nueva columna -->
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>Edad</th>
                    <th>Mascotas</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td> <!-- Mostrando ID -->
                    <td>{{ $usuario->nombre }}</td>
                    <td>{{ $usuario->apellidos }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ \Carbon\Carbon::parse($usuario->fecha_nacimiento)->age }} años</td>
                    <td>{{ $usuario->mascotas->count() }}</td>
                    <td>
                        @switch($usuario->rol)
                            @case('A') Administrador @break
                            @case('V') Veterinario @break
                            @case('G') Groomer @break
                            @case('R') Recepcionista @break
                            @case('U') Usuario @break
                            @default Sin rol
                        @endswitch
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Ver"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-info btn-sm text-white" data-bs-toggle="tooltip" title="Editar"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


@endsection
