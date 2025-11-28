<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'VetCare - Panel Groomer')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --bs-body-font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
      --sidebar-width: 260px;
      --radius-xl: 0.75rem;
      --header-h: 70px;
      --brand: #B35C9C; /* tono distinto para groomer */
      --brand-dark: #8b4a7b;
      --bs-primary: var(--brand);
      --bs-primary-rgb: 179, 92, 156;
      --bs-link-color: var(--brand);
      --bs-link-hover-color: var(--brand-dark);
      --bs-primary-bg-subtle: #f0d8ea;
      --bs-primary-border-subtle: #d4a9c8;
      --bs-primary-text-emphasis: #6a2e57;
    }
    body{ background-color:#f8f9fa; }
    .app{ display:flex; min-height:100vh; overflow:hidden; }
    .sidebar{ width:var(--sidebar-width); background:linear-gradient(180deg,var(--brand),var(--brand-dark)); color:#fff; padding:24px; display:flex; flex-direction:column; position:fixed; top:0; left:0; height:100vh; z-index:1040; }
    .sidebar.collapsed{ width:72px; padding:24px 12px; }
    .sidebar .brand{ display:flex; gap:10px; align-items:center; margin-bottom:24px; transition:all .2s ease; }
    .sidebar .brand-icon{ width:36px; height:36px; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.25); }
    .sidebar .brand-text{ transition:all .2s ease; }
    .sidebar.collapsed .brand-text{ display:none; }
    .sidebar .nav-btn{ width:100%; display:flex; gap:12px; align-items:center; padding:12px 16px; border:0; border-radius:0.75rem; color:#fce7f3; background:transparent; text-align:left; transition:.15s ease; }
    .sidebar .nav-btn.active{ background:rgba(255,255,255,.2); color:#fff; }
    .sidebar .nav-btn:hover{ background:rgba(255,255,255,.1); color:#fff; }
    .sidebar .foot{ margin-top:auto; font-size:.8rem; color:#fce7f3; opacity:.9; }

    .content{ flex:1; display:flex; flex-direction:column; margin-left:var(--sidebar-width); padding-top:var(--header-h); }
    .app-header{ background:#fff; border-bottom:1px solid #e9ecef; padding:16px 32px; position:fixed; top:0; left:var(--sidebar-width); right:0; height:var(--header-h); z-index:1030; display:flex; align-items:center; }
    .app-header > .d-flex{ width:100%; align-items:center; }
    .icon-bubble{ width:48px; height:48px; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; }
    .card-soft{ border-radius: var(--radius-xl); border:1px solid #e9ecef; }

    .sidebar.collapsed + .content{ margin-left:72px; }
    .sidebar.collapsed + .content .app-header{ left:72px; }

    .chart-container{ position:relative; height:250px; }
    .chart-container canvas{ width:100% !important; height:100% !important; display:block; }

    [data-bs-theme="dark"] body { background-color: #1e1f25; color: #e2e2e2; }
    [data-bs-theme="dark"] .app-header { background: #252632; border-color: #333842; }
    [data-bs-theme="dark"] .card-soft { background: #2a2b33; border-color: #3b3d47; }
    [data-bs-theme="dark"] .sidebar { background: linear-gradient(180deg, var(--brand), var(--brand-dark)); }
    [data-bs-theme="dark"] .table> :not(caption)>*>* { color: #e2e2e2; }

    @media (max-width: 992px) {
      .sidebar{ inset:0 auto 0 0; transform:translateX(-100%); transition:transform .2s ease; }
      .sidebar.show{ transform:translateX(0); }
      .content{ margin-left:0 !important; }
      .app-header{ left:0 !important; }
    }
  </style>
  @stack('head')
</head>
<body>
  <div class="app">
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <div class="brand-icon"><i class="bi bi-scissors"></i></div>
        <div class="brand-text">
          <div class="fw-semibold">VetCare</div>
          <div class="small" style="opacity:.85">Panel Groomer</div>
        </div>
      </div>
      <nav class="d-grid gap-2">
        <button class="nav-btn"><i class="bi bi-house" data-section="home"></i><span>Home</span></button>
        <button class="nav-btn"><i class="bi bi-calendar2-week" data-section="agenda"></i><span>Mi Agenda</span></button>
        {{-- <button class="nav-btn"><i class="bi bi-clock" data-section="actividad"></i><span>Actividad de Hoy</span></button> --}}
        <button class="nav-btn"><i class="bi bi-clock-history" data-section="historial"></i><span>Historial</span></button>
        <button class="nav-btn"><i class="bi bi-gear" data-section="configuracion"></i><span>Configuración</span></button>
      </nav>
      <div class="foot pt-4">
        <div>Soporte Groomer</div>
        <div>+1 (555) 123-4567</div>
      </div>
    </aside>

    <div class="content">
      <header class="app-header">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary" id="btnToggleSidebar" aria-label="Alternar menú"><i class="bi bi-list"></i></button>
          </div>
          <div class="header-right d-flex align-items-center gap-3">
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" role="switch" id="switchTheme">
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar"><span>GR</span></div>
              <div>
                <div class="small fw-semibold">{{ $usuario->nombre }}</div>
                <div class="small text-body-secondary">Groomer</div>
              </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button class="btn" aria-label="Salir"><i class="bi bi-box-arrow-right text-danger"></i></button>
            </form>
          </div>
        </div>
      </header>

      <main class="flex-grow-1 overflow-auto p-4 p-lg-5">
        @yield('content')
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function getTextColor(){
      const theme = document.documentElement.getAttribute('data-bs-theme');
      return theme === 'dark' ? '#b5b8bf' : '#6c757d';
    }
    function setupSidebar(){
      const btn = document.getElementById('btnToggleSidebar');
      const sidebar = document.getElementById('sidebar');
      if(!btn || !sidebar) return;
      const savedCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
      if(window.innerWidth >= 992 && savedCollapsed){ sidebar.classList.add('collapsed'); }
      btn.addEventListener('click', ()=>{
        if(window.innerWidth < 992){ sidebar.classList.toggle('show'); }
        else {
          sidebar.classList.toggle('collapsed');
          localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
      });
      document.addEventListener('click', (e)=>{
        if(window.innerWidth >= 992) return;
        if(!sidebar.classList.contains('show')) return;
        const clickInside = sidebar.contains(e.target) || btn.contains(e.target);
        if(!clickInside) sidebar.classList.remove('show');
      });
      window.addEventListener('resize', ()=>{
        if(window.innerWidth < 992){ sidebar.classList.remove('collapsed'); }
        else { sidebar.classList.remove('show'); const saved = localStorage.getItem('sidebarCollapsed') === 'true'; sidebar.classList.toggle('collapsed', saved); }
      });
    }
    function setupTheme(){
      const switchEl = document.getElementById('switchTheme');
      const saved = localStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-bs-theme', saved);
      if(switchEl) switchEl.checked = saved === 'dark';
      switchEl?.addEventListener('change', ()=>{
        const next = switchEl.checked ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
      });
    }
    document.addEventListener('DOMContentLoaded', () => { setupSidebar(); setupTheme(); });
  </script>
  <script src="{{ url('/js/views/dashboard-groomer.js') }}"></script>
  @stack('scripts')
</body>
</html>
