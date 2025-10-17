@extends('layouts.app_admin')

@section('title', 'Dashboard')

@section('aside')
<li class="nav-item mb-2">
  <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
    <i class="bi bi-speedometer2"></i> <span class="nav-text">Dashboard</span>
  </a>
</li>
<li class="nav-item mb-2"><a class="nav-link d-flex align-items-center gap-2" href="{{ route('usuarios') }}"><i class="bi bi-people"></i> <span class="nav-text">Usuarios</span></a></li>
<li class="nav-item mb-2"><a class="nav-link d-flex align-items-center gap-2" href="{{ route('mascotas') }}"><i class="bi bi-basket2"></i> <span class="nav-text">Mascotas</span></a></li>
<li class="nav-item mb-2"><a class="nav-link d-flex align-items-center gap-2" href="{{ route('citas') }}"><i class="bi bi-calendar-event"></i> <span class="nav-text">Citas</span></a></li>
<li class="nav-item mb-2"><a class="nav-link d-flex align-items-center gap-2" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge"></i> <span class="nav-text">Trabajadores</span></a></li>
<li class="nav-item mb-2"><a class="nav-link d-flex align-items-center gap-2" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data"></i> <span class="nav-text">Reportes</span></a></li>
<li class="nav-item mb-2"><a class="nav-link d-flex align-items-center gap-2" href="{{ route('configuracion') }}"><i class="bi bi-gear"></i> <span class="nav-text">Configuración</span></a></li>
@endsection

@section('header-title', 'Dashboard')

@section('content')
<style>
  
  .stats-card h4 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent);
  }

  .stats-card p {
    font-size: 0.95rem;
    color: var(--muted);
  }

  .table thead {
    /* header cells: use the accent-light variable explicitly and force it
       to override Bootstrap or other styles that set a white background */
    color: var(--accent);
    font-weight: 600;
  }

  /* more specific selector targeting the th elements and forcing background */
  .table thead th {
    background-color: var(--accent-light) !important;
    color: var(--accent) !important;
    font-weight: 600;
  }

  /* make the table and body rows transparent so the card background shows
     through (prevents a white table surface from covering the dark card) */
  .card-body .table,
  .card-body .table tbody,
  .card-body .table tbody tr,
  .card-body .table td {
    background-color: transparent !important;
  }

  .table tbody tr:hover {
    background-color: rgba(37, 99, 235, 0.04);
  }

  /* In dark mode, force table text to be white for better contrast */
  html[data-theme="dark"] .card-body .table thead th,
  html[data-theme="dark"] .card-body .table th,
  html[data-theme="dark"] .card-body .table td {
    color: #ffffff !important;
  }

  
</style>

<!-- ===== STATS ===== -->
<div class="row g-4 mt-2">
  @php
    $stats = [
      ['valor' => $numero['citasHoy'], 'texto' => 'Citas Hoy'],
      ['valor' => $numero['citasHoyCompletadas'], 'texto' => 'Citas Completas'],
      ['valor' => $numero['mascotasUltimoMes'], 'texto' => 'Mascotas (Mes)'],
      ['valor' => $numero['clientesNuevosMes'], 'texto' => 'Clientes Nuevos (Mes)'],
    ];
  @endphp
  @foreach ($stats as $s)
  <div class="col-md-3 col-6">
    <div class="card stats-card text-center py-4">
      <div class="card-body">
        <h4>{{ $s['valor'] }}</h4>
        <p>{{ $s['texto'] }}</p>
      </div>
    </div>
  </div>
  @endforeach
</div>

<!-- ===== TABLAS ===== -->
<div class="card shadow-sm mt-5">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Citas de hoy <small class="text-muted">{{ now()->format('d/m/Y') }}</small></span>
    <a href="{{ route('citas') }}" class="fw-semibold" style="color: var(--accent); text-decoration:none;">Ver todas</a>
  </div>
  <div class="card-body p-0">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Hora</th>
          <th>Nombre</th>
          <th>Dueño</th>
          <th>Raza</th>
          <th>Motivo</th>
          <th>Clínica</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($data['citasPendientes'] as $cita)
        <tr>
          <td>{{ \Carbon\Carbon::parse($cita->fecha)->format('h:i A') }}</td>
          <td>{{ $cita->mascota->nombre }}</td>
          <td>{{ $cita->mascota->user->nombre }}</td>
          <td>{{ $cita->mascota->especie }} - {{ $cita->mascota->raza }}</td>
          <td>{{ $cita->servicio->nombre }}</td>
          <td>{{ $cita->clinica->nombre ?? 'Sin clínica' }}</td>
          <td>
            @switch($cita->status)
              @case('confirmada') <span class="badge bg-success">Confirmada</span> @break
              @case('pendiente') <span class="badge bg-warning text-dark">Por confirmar</span> @break
              @case('cancelada') <span class="badge bg-danger">Cancelada</span> @break
              @case('completada') <span class="badge bg-primary">Completada</span> @break
            @endswitch
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-3 text-muted">No hay citas programadas hoy</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
