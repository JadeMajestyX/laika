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

<!-- Tabla de usuarios -->
<div class="card shadow-sm mt-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Edad</th>
                        <th>Mascotas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $cliente)
                    <tr>
                        <td>{{ $cliente->id }}</td>
                        <td>{{ $cliente->nombre }}</td>
                        <td>{{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}</td>
                        <td>{{ $cliente->email }}</td>
                        <td>{{ \Carbon\Carbon::parse($cliente->fecha_nacimiento)->age }} años</td>
                        <td>{{ $cliente->mascotas->count() }}</td>
                        <td>
                            <div class="d-inline-flex gap-1">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Ver"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-info btn-sm text-white" data-bs-toggle="tooltip" title="Editar"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Paginación mejorada -->
<nav class="mt-3 d-flex justify-content-center" aria-label="Paginación">
    <ul class="pagination pagination-sm pagination-rounded shadow-sm">
        {{-- Enlace anterior --}}
        @if ($usuarios->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link"><i class="bi bi-chevron-left"></i> Anterior</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $usuarios->previousPageUrl() }}"><i class="bi bi-chevron-left"></i> Anterior</a>
            </li>
        @endif

        {{-- Páginas --}}
        @foreach ($usuarios->getUrlRange(1, $usuarios->lastPage()) as $page => $url)
            <li class="page-item {{ $page == $usuarios->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
            </li>
        @endforeach

        {{-- Enlace siguiente --}}
        @if ($usuarios->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $usuarios->nextPageUrl() }}">Siguiente <i class="bi bi-chevron-right"></i></a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link">Siguiente <i class="bi bi-chevron-right"></i></span>
            </li>
        @endif
    </ul>
</nav>

@endsection

