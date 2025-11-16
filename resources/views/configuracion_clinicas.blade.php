@extends('layouts.app_admin')

@section('title','Configuración - Clínicas')

@section('aside')
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('citas') }}"><i class="bi bi-calendar-event me-2"></i> Citas</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white bg-white bg-opacity-10 rounded active" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a></li>
@endsection

@section('header-title','Seleccionar Clínica')

@section('content')
<div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Clínicas</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaClinica">
                        <i class="bi bi-plus-lg me-1"></i> Nueva clínica
                </button>
        </div>
        @if(session('success'))
            <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mx-3 mt-3 mb-0">{{ session('error') }}</div>
        @endif
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($clinicas as $clinica)
                    <tr>
                        <td>{{ $clinica->nombre }}</td>
                        <td class="text-muted" style="max-width:220px">{{ Str::limit($clinica->direccion, 40) }}</td>
                        <td>{{ $clinica->telefono ?? '-' }}</td>
                        <td>{{ $clinica->email ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('configuracion.clinica', $clinica->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square me-1"></i> Configurar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay clínicas registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Nueva Clínica -->
<div class="modal fade" id="modalNuevaClinica" tabindex="-1" aria-labelledby="modalNuevaClinicaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaClinicaLabel">Registrar nueva clínica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('configuracion.clinica.store') }}" id="formNuevaClinica">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="ncNombre">Nombre</label>
                            <input type="text" name="nombre" id="ncNombre" class="form-control" required maxlength="100" value="{{ old('nombre') }}">
                            @error('nombre')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ncTelefono">Teléfono</label>
                            <input type="text" name="telefono" id="ncTelefono" class="form-control" required maxlength="15" value="{{ old('telefono') }}">
                            @error('telefono')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ncDireccion">Dirección</label>
                            <input type="text" name="direccion" id="ncDireccion" class="form-control" required maxlength="255" value="{{ old('direccion') }}">
                            @error('direccion')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ncEmail">Email</label>
                            <input type="email" name="email" id="ncEmail" class="form-control" required maxlength="150" value="{{ old('email') }}">
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ncSite">Sitio / URL</label>
                            <input type="text" name="site" id="ncSite" class="form-control" maxlength="255" value="{{ old('site') }}">
                            @error('site')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="ncOpen" name="is_open" value="1" checked>
                                <label class="form-check-label" for="ncOpen">Abierta</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="ncVisible" name="is_visible" value="1" checked>
                                <label class="form-check-label" for="ncVisible">Visible</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
