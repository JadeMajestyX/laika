@extends('layouts.app_admin')

{{-- Título de la página --}}

@section('title', 'Dashboard')

{{-- Aside específico para esta vista --}}
@section('aside')
        <li class="nav-item mb-2">
          <a class="nav-link text-white bg-white bg-opacity-10 rounded active" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="#"><i class="bi bi-people me-2"></i> Usuarios</a>
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

  @section('header-title', 'Inventario')

  @section('content')

      <!-- Stats -->
      <div class="row mt-4">
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <h4>8</h4>
              <p class="mb-0">Citas Hoy</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <h4>18</h4>
              <p class="mb-0">Consultas Hoy</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <h4>124</h4>
              <p class="mb-0">Mascotas registradas hoy</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <h4>8</h4>
              <p class="mb-0">Clientes nuevos (Mes)</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tables -->
      <div class="row mt-4">
        <div class="col-md-12">

          <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between">
              <span>Citas de hoy <small>19/09/2025</small></span>
              <a href="#">Ver todas</a>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Hora</th>
                    <th>Nombre</th>
                    <th>Raza</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>9:00 AM</td>
                    <td>Firulais</td>
                    <td>Canino - Labrador</td>
                    <td>Salud</td>
                    <td><span class="badge bg-success rounded-pill">Confirmada</span></td>
                  </tr>
                  <tr>
                    <td>11:00 AM</td>
                    <td>Chimenea</td>
                    <td>Canino - Rough collie</td>
                    <td>Aseo</td>
                    <td><span class="badge bg-warning text-dark rounded-pill">Por confirmar</span></td>
                  </tr>
                  <tr>
                    <td>17:00 PM</td>
                    <td>Botas</td>
                    <td>Felino - Siamés</td>
                    <td>Aseo</td>
                    <td><span class="badge bg-danger rounded-pill">Cancelada</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
              <span>Consultas de hoy <small>19/09/2025</small></span>
              <a href="#">Ver todas</a>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Hora</th>
                    <th>Nombre</th>
                    <th>Raza</th>
                    <th>Motivo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>9:00 AM</td>
                    <td>Firulais</td>
                    <td>Canino - Labrador</td>
                    <td>Salud</td>
                  </tr>
                  <tr>
                    <td>11:00 AM</td>
                    <td>Chimenea</td>
                    <td>Canino - Rough collie</td>
                    <td>Aseo</td>
                  </tr>
                  <tr>
                    <td>17:00 PM</td>
                    <td>Botas</td>
                    <td>Felino - Siamés</td>
                    <td>Aseo</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
@endsection