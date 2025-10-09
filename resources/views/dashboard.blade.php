@extends('layouts.app_admin')

@section('title', 'Dashboard')

@section('aside')
        <li class="nav-item mb-2">
          <a class="nav-link text-white bg-white bg-opacity-10 rounded active" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a>
        </li>
@endsection

@section('header-title', 'Dashboard')

@section('content')

      <!-- Stats -->
      <div class="row mt-4">
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <h4>{{$numero['citasHoy']}}</h4>
              <p class="mb-0">Citas Hoy</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <h4>{{$numero['citasHoyCompletadas']}}</h4>
              <p class="mb-0">Citas completas</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <h4>{{$numero['mascotasUltimoMes']}}</h4>
              <p class="mb-0">Mascotas registradas (Mes)</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <h4>{{$numero['clientesNuevosMes']}}</h4>
              <p class="mb-0">Clientes nuevos (Mes)</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tables -->
      <div class="row mt-4">
        <div class="col-md-12">

<!-- Citas de hoy -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between">
        <span>Citas de hoy <small>{{ now()->format('d/m/Y') }}</small></span>
        <a href="#">Ver todas</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Hora</th>
                    <th>Nombre</th>
                    <th>Dueño</th>
                    <th>Raza</th>
                    <th>Motivo</th>
                    <th>Clínica</th> <!-- Nueva columna -->
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['citasPendientes'] as $cita)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($cita->fecha)->format('h:i A') }}</td>
                    <td>{{ $cita->mascota->nombre }}</td>
                    <td>{{ $cita->mascota->user->nombre }}</td>
                    <td>{{ $cita->mascota->especie}} - {{ $cita->mascota->raza }}</td>
                    <td>{{ $cita->servicio->nombre }}</td>
                    <td>{{ $cita->clinica->nombre ?? 'Sin clínica' }}</td> <!-- Aquí se muestra la clínica -->
                    <td>
                        @if ($cita->status === 'confirmada')
                            <span class="badge bg-success rounded-pill">Confirmada</span>
                        @elseif ($cita->status === 'pendiente')
                            <span class="badge bg-warning text-dark rounded-pill">Por confirmar</span>
                        @elseif ($cita->status === 'cancelada')
                            <span class="badge bg-danger rounded-pill">Cancelada</span>
                        @elseif ($cita->status === 'completada')
                            <span class="badge bg-primary rounded-pill">Completada</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Realizadas de hoy -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <span>Realizadas de hoy <small>{{ now()->format('d/m/Y') }}</small></span>
        <a href="#">Ver todas</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Hora</th>
                    <th>Nombre</th>
                    <th>Dueño</th>
                    <th>Raza</th>
                    <th>Motivo</th>
                    <th>Clínica</th> <!-- Nueva columna -->
                </tr>
            </thead>
            <tbody>
                @foreach ($data['citasCompletadas'] as $cita)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($cita->fecha)->format('h:i A') }}</td>
                    <td>{{ $cita->mascota->nombre }}</td>
                    <td>{{ $cita->mascota->user->nombre }}</td>
                    <td>{{ $cita->mascota->especie}} - {{ $cita->mascota->raza }}</td>
                    <td>{{ $cita->servicio->nombre }}</td>
                    <td>{{ $cita->clinica->nombre ?? 'Sin clínica' }}</td> <!-- Mostrar clínica -->
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
