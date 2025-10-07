<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Laika – Mascotas</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    body{background-color:#f8f9fa;}
    /* === mismo aside === */
    .sidebar{background:#6f42c1;min-height:100vh;color:#fff;}
    .sidebar .nav-link{color:#fff;}
    .sidebar .nav-link.active{background:rgba(255,255,255,.2);border-radius:5px;}
    /* table + acciones */
    .table thead th{font-weight:600;}
    .actions .btn{--bs-btn-padding-y:.25rem; --bs-btn-padding-x:.5rem}
    .avatar-badge{width:40px;height:40px;border-radius:50%;display:inline-grid;place-items:center;}
    .search-wrap{max-width:340px}
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- === Sidebar (idéntico) === -->
    <nav class="col-md-2 d-none d-md-block sidebar p-3">
      <h4 class="mb-4">Laika</h4>
      <ul class="nav flex-column">
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link active" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a>
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

    <!-- === Main === -->
    <main class="col-md-10 ms-sm-auto px-4">
      <!-- Topbar -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
        <h2 class="mb-0">Mascotas</h2>
        <div class="d-flex align-items-center gap-3">
          <div class="search-wrap">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" placeholder="Buscar...">
              <button class="btn btn-outline-secondary"><i class="bi bi-filter"></i></button>
            </div>
          </div>
          <i class="bi bi-bell fs-4"></i>
          <span class="badge bg-secondary avatar-badge">CJ</span>
          <span>Administrador</span>
        </div>
      </div>

      <!-- Tabla -->
      <div class="card shadow-sm mt-4">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>Nombre</th>
                  <th>Especie</th>
                  <th>Raza</th>
                  <th>Edad</th>
                  <th>Peso</th>
                  <th>Dueño</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Firulais</td>
                  <td>Canino</td>
                  <td>Labrador</td>
                  <td>3 años</td>
                  <td>42 kg</td>
                  <td>Pedro</td>
                  <td class="text-center">
                    <div class="actions d-inline-flex gap-1">
                      <button class="btn btn-warning" data-bs-toggle="tooltip" data-bs-title="Ver"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-info text-white" data-bs-toggle="tooltip" data-bs-title="Editar"><i class="bi bi-pencil-square"></i></button>
                      <button class="btn btn-danger" data-bs-toggle="tooltip" data-bs-title="Eliminar"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>Julio</td>
                  <td>Felino</td>
                  <td>Siamés</td>
                  <td>5 años</td>
                  <td>6 kg</td>
                  <td>Jaime</td>
                  <td class="text-center">
                    <div class="actions d-inline-flex gap-1">
                      <button class="btn btn-warning" data-bs-toggle="tooltip" data-bs-title="Ver"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-info text-white" data-bs-toggle="tooltip" data-bs-title="Editar"><i class="bi bi-pencil-square"></i></button>
                      <button class="btn btn-danger" data-bs-toggle="tooltip" data-bs-title="Eliminar"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
                <!-- más filas... -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <nav class="mt-3" aria-label="Paginación">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Anterior</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item"><a class="page-link" href="#">Siguiente</a></li>
        </ul>
      </nav>

    </main>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el=>new bootstrap.Tooltip(el));
</script>
</body>
</html>