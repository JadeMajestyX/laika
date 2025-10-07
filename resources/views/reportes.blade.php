<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reportes y estadísticas - Laika</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      background: #6f42c1;
      min-height: 100vh;
      color: #fff;
    }
    .sidebar .nav-link {
      color: #fff;
    }
    .sidebar .nav-link.active {
      background: rgba(255,255,255,0.2);
      border-radius: 5px;
    }
    .card {
      border-radius: 10px;
    }
    .metric-card {
      background: #fff;
      border-radius: 10px;
      text-align: center;
      padding: 20px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .metric-icon {
      font-size: 2rem;
      color: #6f42c1;
    }
    .filter-box input, .filter-box select, .filter-box button {
      font-size: 0.9rem;
    }
    .filter-box .btn-purple {
      background-color: #6f42c1;
      color: white;
    }
    .table th {
      color: #6f42c1;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 d-none d-md-block sidebar p-3">
      <h4 class="mb-4">Laika</h4>
      <ul class="nav flex-column">
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="#"><i class="bi bi-people me-2"></i> Usuarios</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link active" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a>
        </li>
      </ul>
    </nav>

    <!-- Main content -->
    <main class="col-md-10 ms-sm-auto px-4">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <h2>Reportes y estadísticas</h2>
        <div class="d-flex align-items-center">
          <i class="bi bi-bell me-3 fs-4"></i>
          <span class="badge bg-secondary rounded-circle p-3">CJ</span>
          <span class="ms-2">Administrador</span>
        </div>
      </div>

      <!-- Filtros -->
      <div class="card shadow-sm mt-4 p-3 filter-box">
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
            <button class="btn btn-purple me-2"><i class="bi bi-funnel"></i> Aplicar filtro</button>
            <button class="btn btn-outline-secondary"><i class="bi bi-download"></i> Exportar</button>
          </div>
        </div>
      </div>

      <!-- Métricas -->
      <div class="row mt-4">
        <div class="col-md-3">
          <div class="metric-card">
            <i class="bi bi-calendar-check metric-icon"></i>
            <h5 class="mt-2">8</h5>
            <p class="text-muted mb-0">Citas realizadas</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card">
            <i class="bi bi-heart-pulse metric-icon"></i>
            <h5 class="mt-2">18</h5>
            <p class="text-muted mb-0">Mascotas atendidas</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card">
            <i class="bi bi-person-check metric-icon"></i>
            <h5 class="mt-2">124</h5>
            <p class="text-muted mb-0">Clientes nuevos</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card">
            <i class="bi bi-cash-stack metric-icon"></i>
            <h5 class="mt-2">$10,457</h5>
            <p class="text-muted mb-0">Ingresos totales</p>
          </div>
        </div>
      </div>

      <!-- Gráficas -->
      <div class="row mt-4">
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

      <div class="row mt-4">
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

      <!-- Resumen de citas -->
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
              <tr>
                <td>Realizadas</td>
                <td>132</td>
                <td>78%</td>
                <td class="text-success">↑ 5%</td>
              </tr>
              <tr>
                <td>Canceladas</td>
                <td>21</td>
                <td>23%</td>
                <td class="text-success">↑ 2%</td>
              </tr>
              <tr>
                <td>No presentadas</td>
                <td>3</td>
                <td>5%</td>
                <td class="text-success">↑ 1%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Servicios más solicitados -->
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
              <tr>
                <td>Consulta</td>
                <td>132</td>
                <td>$4,556</td>
                <td class="text-success">↑ 5%</td>
              </tr>
              <tr>
                <td>Cita</td>
                <td>21</td>
                <td>$2,656</td>
                <td class="text-success">↑ 2%</td>
              </tr>
              <tr>
                <td>Aseo</td>
                <td>23</td>
                <td>$1,358</td>
                <td class="text-success">↑ 1%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Librerías -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Gráficas con Chart.js -->
<script>
  const ctxCitas = document.getElementById('chartCitas');
  new Chart(ctxCitas, {
    type: 'pie',
    data: {
      labels: ['Consulta general', 'Esterilización', 'Cirugía', 'Urgencias'],
      datasets: [{
        data: [40, 25, 20, 15],
        backgroundColor: ['#6f42c1', '#dc3545', '#0d6efd', '#20c997']
      }]
    }
  });

  const ctxMascotas = document.getElementById('chartMascotas');
  new Chart(ctxMascotas, {
    type: 'pie',
    data: {
      labels: ['Perros', 'Gatos', 'Aves', 'Reptiles', 'Roedores'],
      datasets: [{
        data: [50, 30, 10, 5, 5],
        backgroundColor: ['#6f42c1', '#198754', '#ffc107', '#0d6efd', '#dc3545']
      }]
    }
  });

  const ctxIngresos = document.getElementById('chartIngresos');
  new Chart(ctxIngresos, {
    type: 'bar',
    data: {
      labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
      datasets: [{
        label: 'Ingresos en $',
        data: [8500, 9200, 8700, 9600, 10300, 9900],
        backgroundColor: '#6f42c1'
      }]
    }
  });

  const ctxProductos = document.getElementById('chartProductos');
  new Chart(ctxProductos, {
    type: 'bar',
    data: {
      labels: ['Alimento perro', 'Antipulgas', 'Arena gatos', 'Juguetes', 'Shampoo'],
      datasets: [{
        label: 'Unidades vendidas',
        data: [90, 70, 60, 50, 40],
        backgroundColor: '#28a745'
      }]
    },
    options: {
      indexAxis: 'y'
    }
  });
</script>

</body>
</html>
