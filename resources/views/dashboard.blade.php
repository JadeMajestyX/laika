<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Laika</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
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
    .status {
      padding: 5px 10px;
      border-radius: 15px;
      color: #fff;
      font-size: 0.9rem;
    }
    .status.confirmada {
      background: #28a745;
    }
    .status.por-confirmar {
      background: #ffc107;
      color: #000;
    }
    .status.cancelada {
      background: #dc3545;
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
          <a class="nav-link active" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
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
          <a class="nav-link" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a>
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
        <h2>Dashboard</h2>
        <div>
          <i class="bi bi-bell me-3 fs-4"></i>
          <span class="badge bg-secondary rounded-circle p-3">CJ</span>
          <span class="ms-2">Administrador</span>
        </div>
      </div>

      <!-- Stats -->
      <div class="row mt-4">
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm">
            <div class="card-body text-center">
              <h4>8</h4>
              <p class="mb-0">Citas Hoy</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm">
            <div class="card-body text-center">
              <h4>18</h4>
              <p class="mb-0">Consultas Hoy</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm">
            <div class="card-body text-center">
              <h4>124</h4>
              <p class="mb-0">Mascotas registradas hoy</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm">
            <div class="card-body text-center">
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
                    <td><span class="status confirmada">Confirmada</span></td>
                  </tr>
                  <tr>
                    <td>11:00 AM</td>
                    <td>Chimenea</td>
                    <td>Canino - Rough collie</td>
                    <td>Aseo</td>
                    <td><span class="status por-confirmar">Por confirmar</span></td>
                  </tr>
                  <tr>
                    <td>17:00 PM</td>
                    <td>Botas</td>
                    <td>Felino - Siamés</td>
                    <td>Aseo</td>
                    <td><span class="status cancelada">Cancelada</span></td>
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

        </div>
      </div>

    </main>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
