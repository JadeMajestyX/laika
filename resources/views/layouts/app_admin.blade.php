<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Laika</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Fuente moderna -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        
        :root {
            --primary: #6D28D9;
            --primary-hover: #7C3AED;
            --accent: #2563EB;
            --accent-light: rgba(37, 99, 235, 0.06);
            --sidebar-bg: #4C1D95;
            --sidebar-hover: rgba(255, 255, 255, 0.15);
            --sidebar-text: #F9FAFB;
            --bg: #F8FAFC;
            --card-bg: #FFFFFF;
            --text: #0F172A;
            --muted: #64748B;
            --border-color: #E2E8F0;
        }

        [data-theme="dark"] {
            --primary: #8B5CF6;
            --primary-hover: #A78BFA;
            --accent: #3B82F6;
            --accent-light: rgba(59, 130, 246, 0.06);
            --sidebar-bg: #1E1B4B;
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --sidebar-text: #E2E8F0;
            --bg: #0F172A;
            --card-bg: #1E293B;
            --text: #E2E8F0;
            --muted: #94A3B8;
            --border-color: #334155;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            transition: background 0.3s, color 0.3s;
        }

        /* For dark theme: force important UI text colors to white for contrast */
        html[data-theme="dark"] .sidebar .nav-link,
        html[data-theme="dark"] .sidebar .nav-link .nav-text,
        html[data-theme="dark"] .header h5,
        html[data-theme="dark"] .header .fw-semibold,
        html[data-theme="dark"] .stats-card h4,
        html[data-theme="dark"] .stats-card p,
        html[data-theme="dark"] .card .card-header,
        html[data-theme="dark"] .card .card-header a,
        html[data-theme="dark"] .card .card-header small {
            color: #ffffff !important;
        }

        /* Icons inside sidebar links */
        html[data-theme="dark"] .sidebar .nav-link i,
        html[data-theme="dark"] .sidebar .nav-link .bi {
            color: #ffffff !important;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100vh;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            padding-top: 70px;
            transition: width 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 72px;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 10px 18px;
            border-radius: 10px;
            margin: 3px 12px;
            transition: background 0.2s ease, transform 0.2s;
        }

        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover);
            transform: translateX(3px);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-hover);
            font-weight: 600;
        }

        .sidebar.collapsed .nav-text {
            display: none;
        }

        /* ===== HEADER ===== */
        .header {
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            height: 70px;
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1rem;
            transition: left 0.3s ease;
            z-index: 900;
        }

        .sidebar.collapsed ~ .header {
            left: 72px;
        }

        .toggle-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text);
            cursor: pointer;
        }

        /* ===== MAIN ===== */
        main {
            margin-left: 240px;
            padding: 90px 25px;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ main {
            margin-left: 72px;
        }

        .card {
            border: none;
            border-radius: 18px;
            background-color: var(--card-bg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        .btn-outline-secondary {
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .header {
                left: 0 !important;
            }

            main {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body>
    <!-- ===== SIDEBAR ===== -->
    <nav class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            @yield('aside')
        </ul>
    </nav>

    <!-- ===== HEADER ===== -->
    <header class="header" id="header">
        <div class="d-flex align-items-center">
            <button class="toggle-btn me-3" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0 fw-semibold">@yield('header-title')</h5>
        </div>

        <div class="d-flex align-items-center">
            <i class="bi bi-bell me-3 fs-5"></i>

            <!-- Botón modo oscuro -->
            <button class="btn btn-outline-secondary btn-sm me-3" id="toggleTheme" title="Cambiar tema">
                <i class="bi bi-moon"></i>
            </button>

            <!-- Perfil -->
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-75 text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                    {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                </div>
                <span class="fw-semibold">{{ $usuario->nombre }}</span>
            </div>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="ms-3 d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </button>
            </form>
        </div>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main id="main-content">
        @yield('content')
    </main>

    <!-- ===== JS ===== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');

        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                sidebar.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
                document.querySelector('.header').classList.toggle('collapsed');
                document.querySelector('main').classList.toggle('collapsed');
            }
        });

        // Modo oscuro
        const themeToggle = document.getElementById('toggleTheme');
        const html = document.documentElement;
        const currentTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', currentTheme);

        const updateIcon = () => {
            themeToggle.innerHTML = html.getAttribute('data-theme') === 'dark'
                ? '<i class="bi bi-sun"></i>'
                : '<i class="bi bi-moon"></i>';
        };
        updateIcon();

        themeToggle.addEventListener('click', () => {
            const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon();
        });
    </script>

    @yield('scripts')
</body>
</html>
