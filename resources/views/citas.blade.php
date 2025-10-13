@extends('layouts.app_admin')

{{-- Título de la página --}}
@section('title', 'Citas')

{{-- Aside específico para esta vista --}}
@section('aside')
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white bg-white bg-opacity-10 rounded active" href="{{ route('citas') }}"><i class="bi bi-calendar-event me-2"></i> Citas</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a></li>
@endsection

@section('header-title', 'Citas')

@section('content')

<!-- Encabezado con botón -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <h5 class="fw-bold mb-0">Listado de citas registradas</h5>
    <a href="" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Nueva cita
    </a>
</div>

<!-- Tabla de citas -->
<div class="card shadow-sm mt-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaCitas" class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Clínica</th>
                        <th>Servicio</th>
                        <th>Mascota</th>
                        <th>Propietario</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($citas as $cita)
                    <tr>
                        <td>{{ $cita->id }}</td>
                        <td>{{ $cita->clinica->nombre ?? 'N/A' }}</td>
                        <td>{{ $cita->servicio->nombre ?? 'N/A' }}</td>
                        <td>{{ $cita->mascota->nombre ?? 'N/A' }}</td>
                        <td>{{ $cita->mascota->usuario->nombre ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($cita->status === 'pendiente')
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @elseif($cita->status === 'completada')
                                <span class="badge bg-success">Completada</span>
                            @elseif($cita->status === 'cancelada')
                                <span class="badge bg-danger">Cancelada</span>
                            @else
                                <span class="badge bg-secondary">Desconocido</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-inline-flex gap-1">
                                <!-- Ver -->
                                <a href="" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <!-- Editar -->
                                <a href="" class="btn btn-info btn-sm text-white" data-bs-toggle="tooltip" title="Editar cita">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <!-- Eliminar -->
                                <form action="" >
                                    {{-- @csrf
                                    @method('DELETE') --}}
                                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar cita">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No hay citas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Paginación -->
@if ($citas->hasPages())
<nav class="mt-3 d-flex justify-content-center" aria-label="Paginación">
    <ul class="pagination pagination-sm pagination-rounded shadow-sm">
        {{-- Anterior --}}
        @if ($citas->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link"><i class="bi bi-chevron-left"></i> Anterior</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $citas->previousPageUrl() }}"><i class="bi bi-chevron-left"></i> Anterior</a>
            </li>
        @endif

        {{-- Páginas --}}
        @foreach ($citas->getUrlRange(1, $citas->lastPage()) as $page => $url)
            <li class="page-item {{ $page == $citas->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
            </li>
        @endforeach

        {{-- Siguiente --}}
        @if ($citas->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $citas->nextPageUrl() }}">Siguiente <i class="bi bi-chevron-right"></i></a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link">Siguiente <i class="bi bi-chevron-right"></i></span>
            </li>
        @endif
    </ul>
</nav>
@endif

@endsection
