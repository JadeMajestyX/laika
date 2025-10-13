@extends('layouts.app_admin')

{{-- Título de la página --}}

@section('title', 'Reportes')

{{-- Aside específico para esta vista --}}
@section('aside')
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a></li>
        {{-- <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a></li> --}}
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white bg-white bg-opacity-10 rounded active" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a></li>
@endsection


  @section('header-title', 'Reportes')

  @section('content')

      <!-- Filtros -->
      <div class="card shadow-sm mt-4 p-3">
        <div class="row align-items-end">
          <div class="col-md-2">
            <label class="form-label">Rango de fechas:</label>
            <select class="form-select">
              <option>Este mes</option>
              <option>Último mes</option>
              <option>Últimos 3 meses</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Desde:</label>
            <input type="date" class="form-control" value="2025-09-01">
          </div>
          <div class="col-md-2">
            <label class="form-label">Hasta:</label>
            <input type="date" class="form-control" value="2025-09-15">
          </div>
          <div class="col-md-2">
            <label class="form-label">Rol:</label>
            <select class="form-select">
              <option>Veterinario</option>
              <option>Groomer</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Trabajador:</label>
            <input type="text" class="form-control" value="Dr. House">
          </div>
          <div class="col-md-2 text-end">
            <button class="btn text-white" style="background:#6f42c1;"><i class="bi bi-funnel"></i> Aplicar filtro</button>
            <button class="btn btn-outline-secondary"><i class="bi bi-download"></i> Exportar</button>
          </div>
        </div>
      </div>

      <!-- Métricas -->
      <div class="row mt-4 g-3">
        <div class="col-md-3">
          <div class="card text-center p-3 shadow-sm">
            <i class="bi bi-calendar-check fs-2 text-purple"></i>
            <h5 class="mt-2">8</h5>
            <p class="text-muted mb-0">Citas realizadas</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center p-3 shadow-sm">
            <i class="bi bi-heart-pulse fs-2 text-purple"></i>
            <h5 class="mt-2">18</h5>
            <p class="text-muted mb-0">Mascotas atendidas</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center p-3 shadow-sm">
            <i class="bi bi-person-check fs-2 text-purple"></i>
            <h5 class="mt-2">124</h5>
            <p class="text-muted mb-0">Clientes nuevos</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center p-3 shadow-sm">
            <i class="bi bi-cash-stack fs-2 text-purple"></i>
            <h5 class="mt-2">$10,457</h5>
            <p class="text-muted mb-0">Ingresos totales</p>
          </div>
        </div>
      </div>

      <!-- Gráficas -->
      <div class="row mt-4 g-3">
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold">Citas por servicio</h6>
            <canvas id="chartCitas"></canvas>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold">Mascotas por especie</h6>
            <canvas id="chartMascotas"></canvas>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold">Ingresos mensuales</h6>
            <canvas id="chartIngresos"></canvas>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold">Productos más vendidos</h6>
            <canvas id="chartProductos"></canvas>
          </div>
        </div>
      </div>

      <!-- Tablas resumen -->
      <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Resumen de citas</span>
          <a href="#">Ver todos</a>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Estado</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
                <th>Tendencia</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Realizadas</td><td>132</td><td>78%</td><td class="text-success">↑ 5%</td></tr>
              <tr><td>Canceladas</td><td>21</td><td>23%</td><td class="text-success">↑ 2%</td></tr>
              <tr><td>No presentadas</td><td>3</td><td>5%</td><td class="text-success">↑ 1%</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm mt-4 mb-5">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Servicios más solicitados</span>
          <a href="#">Ver todos</a>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Servicio</th>
                <th>Cantidad</th>
                <th>Ingresos</th>
                <th>Variación</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Consulta</td><td>132</td><td>$4,556</td><td class="text-success">↑ 5%</td></tr>
              <tr><td>Cita</td><td>21</td><td>$2,656</td><td class="text-success">↑ 2%</td></tr>
              <tr><td>Aseo</td><td>23</td><td>$1,358</td><td class="text-success">↑ 1%</td></tr>
            </tbody>
          </table>
        </div>
      </div>

@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/reportes.js') }}"></script>
@endsection
</body>
</html>
