<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios - Laika</title>
  <!-- Bootstrap CSS -->
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
    .action-btn {
      border: none;
      padding: 5px 8px;
      border-radius: 5px;
      color: #fff;
      margin-right: 5px;
    }
    .view { background: #ffc107; color: #000; }
    .edit { background: #0d6efd; }
    .delete { background: #dc3545; }
    .card-header {
      font-weight: 600;
      font-size: 1rem;
    }
    .add-btn {
      background: #6f42c1;
      border: none;
      border-radius: 50%;
      color: #fff;
      width: 35px;
      height: 35px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
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
          <a class="nav-link active" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a>
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
        <h2>Usuarios</h2>
        <div class="d-flex align-items-center">
          <input type="text" class="form-control me-3" placeholder="Buscar ...">
          <i class="bi bi-bell me-3 fs-4"></i>
          <span class="badge bg-secondary rounded-circle p-3">CJ</span>
          <span class="ms-2">Administrador</span>
        </div>
      </div>

      <!-- Tabla de trabajadores -->
      <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Usuarios Registrados</span>
          <button class="add-btn"><i class="bi bi-plus-lg"></i></button>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Correo</th>
                <th>Edad</th>
                <th>Mascotas</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Pedro</td>
                <td>Hernandez</td>
                <td>Hrm123@gmail.com</td>
                <td>27 años</td>
                <td>3</td>
                <td>
                  <button class="action-btn view"><i class="bi bi-eye"></i></button>
                  <button class="action-btn edit"><i class="bi bi-file-earmark-text"></i></button>
                  <button class="action-btn delete"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
              <tr>
                <td>Jaime</td>
                <td>Sanchez</td>
                <td>Sanz123@gmail.com</td>
                <td>25 años</td>
                <td>1</td>
                <td>
                  <button class="action-btn view"><i class="bi bi-eye"></i></button>
                  <button class="action-btn edit"><i class="bi bi-file-earmark-text"></i></button>
                  <button class="action-btn delete"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
