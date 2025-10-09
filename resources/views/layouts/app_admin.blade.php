<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Laika</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        {{-- Aside dinámico --}}
        <nav class="col-md-2 d-none d-md-block text-white min-vh-100 p-3" style="background-color:#6f42c1;">
            <h4 class="mb-4">Laika</h4>
            <ul class="nav flex-column">
                @yield('aside') {{-- Aquí inyectaremos los links según la vista --}}
            </ul>
        </nav>

        {{-- Main content --}}
        <main class="col-md-10 ms-sm-auto px-4">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h2>@yield('header-title')</h2>
                <div class="d-flex align-items-center">
                    <input type="text" class="form-control me-3" placeholder="Buscar ...">
                    <i class="bi bi-bell me-3 fs-4"></i>
                    <span class="badge bg-secondary rounded-circle p-3">CJ</span>
                    <span class="ms-2">{{ $usuario->nombre }}</span>
                </div>
            </div>

            {{-- Contenido específico de cada vista --}}
            @yield('content')
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
