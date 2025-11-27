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
          <div class="col-md-4 d-flex justify-content-end gap-2">
              <button id="btn-aplicar-filtro" class="btn text-white" style="background:#6f42c1;"><i class="bi bi-funnel"></i> Aplicar filtro</button>
              <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-download"></i> Exportar
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="#" id="btn-exportar-pdf">Exportar PDF</a></li>
                </ul>
              </div>
            </div>

        </div>
      </div>

      <!-- Métricas -->
      <div class="row mt-4 g-3">
        <div class="col-md-4">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-calendar-check"></i></div></div>
            <h4 id="metric-citas-realizadas" class="mt-2 mb-0">—</h4>
                <div class="text-body-secondary">Citas atendidas</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-heart-pulse"></i></div></div>
            <h4 id="metric-mascotas-atendidas" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Mascotas atendidas</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-person-check"></i></div></div>
            <h4 id="metric-clientes-nuevos" class="mt-2 mb-0">—</h4>
                <div class="text-body-secondary">Usuarios nuevos</div>
          </div>
        </div>
      </div>

      <!-- Secciones resumidas -->
      <div class="row mt-4 g-3">
        <div class="col-md-6">
          <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="fw-bold mb-1">Citas atendidas</h6>
                <div class="display-6 mb-1" id="panel-citas-atendidas">—</div>
                <p class="text-body-secondary small mb-0">Total de citas completadas en el rango seleccionado.</p>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-success-subtle text-success">
                <i class="bi bi-clipboard2-check"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="fw-bold mb-1">Usuarios nuevos</h6>
                <div class="display-6 mb-1" id="panel-usuarios-nuevos">—</div>
                <p class="text-body-secondary small mb-0">Clientes registrados en el periodo consultado.</p>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-primary-subtle text-primary">
                <i class="bi bi-person-plus"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficas -->
      <div class="row mt-4 g-3">
        <div class="col-lg-6">
          <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <h6 class="fw-bold mb-1">Distribución por estado</h6>
                <p class="text-body-secondary small mb-0">Proporción de citas según su estado actual.</p>
              </div>
              <span class="badge bg-light text-body-secondary">Citas</span>
            </div>
            <div class="chart-wrap">
              <canvas id="chart-resumen-citas"></canvas>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <h6 class="fw-bold mb-1">Indicadores clave</h6>
                <p class="text-body-secondary small mb-0">Comparativa de las métricas principales.</p>
              </div>
              <span class="badge bg-light text-body-secondary">Top 3</span>
            </div>
            <div class="chart-wrap">
              <canvas id="chart-metricas"></canvas>
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

      <div class="mb-5"></div>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/reportes.js') }}"></script>
@endpush
