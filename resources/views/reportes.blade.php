@extends('layouts.app_admin')

{{-- Título de la página --}}

@section('title', 'Reportes')

{{-- Aside específico para esta vista --}}
@section('aside')
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white " href="{{ route('citas') }}"><i class="bi bi-calendar-event me-2"></i> Citas</a></li>
        {{-- <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a></li> --}}
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white bg-white bg-opacity-10 rounded active" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a></li>
@endsection


  @section('header-title', 'Reportes')

  @push('head')
  <style>
    .text-purple{ color:#6f42c1 !important; }
    .metric-card{ border:1px solid #e9ecef; border-radius: var(--radius-xl); }
    .metric-card .icon-bubble{ width:46px; height:46px; border-radius: .75rem; display:flex; align-items:center; justify-content:center; background: rgba(111,66,193,.12); color:#6f42c1; }
    .skeleton{ position:relative; overflow:hidden; background:#e9ecef; color:transparent !important; border-radius:.25rem; }
    .skeleton::after{ content:""; position:absolute; inset:0; transform:translateX(-100%); background:linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent); animation:shimmer 1.2s infinite; }
    @keyframes shimmer{ 100%{ transform:translateX(100%);} }
    .chart-wrap{ position:relative; height:280px; }
    .chart-wrap canvas{ width:100% !important; height:100% !important; }
    .chart-spinner{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:transparent; }
    .table thead th{ font-weight:600; }
  </style>
  @endpush

  @section('content')

    <!-- Encabezado -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <h2 class="mb-1">Reportes</h2>
        <div class="text-body-secondary">Analiza el rendimiento por fechas, servicios y personal.</div>
      </div>
      <div class="small text-body-secondary" id="texto-rango"></div>
    </div>

      <!-- Filtros -->
      <div class="card shadow-sm mt-4 p-3">
        <div class="row align-items-end">
          <div class="col-md-2">
            <label class="form-label">Rango de fechas:</label>
            <select id="filtro-rango" class="form-select">
              <option value="mes-actual" selected>Este mes</option>
              <option value="mes-anterior">Último mes</option>
              <option value="3-meses">Últimos 3 meses</option>
              <option value="custom">Personalizado</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Desde:</label>
            <input id="filtro-desde" type="date" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">Hasta:</label>
            <input id="filtro-hasta" type="date" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">Rol:</label>
            <select id="filtro-rol" class="form-select">
              <option value="">Todos</option>
              @isset($roles)
                @foreach($roles as $rol)
                  <option value="{{ $rol }}">{{ ucfirst($rol) }}</option>
                @endforeach
              @endisset
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Trabajador (ID):</label>
            <input id="filtro-trabajador" type="number" class="form-control" placeholder="Ej. 12">
          </div>
          
            <div class="col-md-2 d-flex justify-content-end gap-2">
              <button id="btn-aplicar-filtro" class="btn text-white" style="background:#6f42c1;"><i class="bi bi-funnel"></i> Aplicar filtro</button>
              <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-download"></i> Exportar
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="#" id="btn-exportar-xlsx">Exportar XLSX</a></li>
                  <li><a class="dropdown-item" href="#" id="btn-exportar-pdf">Exportar PDF</a></li>
                </ul>
              </div>
            </div>

        </div>
      </div>

      <!-- Métricas -->
      <div class="row mt-4 g-3">
        <div class="col-md-3">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-calendar-check"></i></div></div>
            <h4 id="metric-citas-realizadas" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Citas realizadas</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-heart-pulse"></i></div></div>
            <h4 id="metric-mascotas-atendidas" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Mascotas atendidas</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-person-check"></i></div></div>
            <h4 id="metric-clientes-nuevos" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Clientes nuevos</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-cash-stack"></i></div></div>
            <h4 id="metric-ingresos-totales" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Ingresos totales</div>
          </div>
        </div>
      </div>

      <!-- Gráficas -->
      <div class="row mt-4 g-3">
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold mb-2">Citas por servicio</h6>
            <div class="chart-wrap">
              <div id="spinner-citas" class="chart-spinner d-none"><div class="spinner-border" role="status"></div></div>
              <canvas id="chartCitas"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold mb-2">Mascotas por especie</h6>
            <div class="chart-wrap">
              <div id="spinner-mascotas" class="chart-spinner d-none"><div class="spinner-border" role="status"></div></div>
              <canvas id="chartMascotas"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold mb-2">Ingresos mensuales</h6>
            <div class="chart-wrap">
              <div id="spinner-ingresos" class="chart-spinner d-none"><div class="spinner-border" role="status"></div></div>
              <canvas id="chartIngresos"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold mb-2">Servicios más solicitados</h6>
            <div class="chart-wrap">
              <div id="spinner-servicios" class="chart-spinner d-none"><div class="spinner-border" role="status"></div></div>
              <canvas id="chartProductos"></canvas>
            </div>
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
            <tbody id="tabla-resumen-citas">
              <tr class="placeholder-row"><td colspan="4" class="text-center text-body-secondary">Cargando…</td></tr>
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
            <tbody id="tabla-servicios-top">
              <tr class="placeholder-row"><td colspan="4" class="text-center text-body-secondary">Cargando…</td></tr>
            </tbody>
          </table>
        </div>
      </div>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/reportes.js') }}"></script>
@endpush
