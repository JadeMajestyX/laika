<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'VetCare - Panel')</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- Fuente opcional -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --bs-body-font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
      --sidebar-width: 260px;
      --radius-xl: 0.75rem;
      --header-h: 70px;

      /* Paleta personalizada */
      --brand: #3A7CA5;
      --brand-dark: #2f6485; /* tono más oscuro para gradiente/hover */
      /* Override Bootstrap primary */
      --bs-primary: var(--brand);
      --bs-primary-rgb: 58, 124, 165;
      --bs-link-color: var(--brand);
      --bs-link-hover-color: var(--brand-dark);
      /* Utilidades subtle de Bootstrap 5.3 */
      --bs-primary-bg-subtle: #d7eaf4; /* fondo claro */
      --bs-primary-border-subtle: #a7c9dc;
      --bs-primary-text-emphasis: #1f4f6a;
    }
    body{ background-color:#f8f9fa; }
    .app{ display:flex; min-height:100vh; overflow:hidden; }
    /* Sidebar */
  .sidebar{ width:var(--sidebar-width); background:linear-gradient(180deg,var(--brand),var(--brand-dark)); color:#fff; padding:24px; display:flex; flex-direction:column; position:fixed; top:0; left:0; height:100vh; z-index:1040; }
    .sidebar.collapsed{ width:72px; padding:24px 12px; }
    .sidebar.collapsed .brand{ justify-content:center; }
    .sidebar .brand{ transition:all .2s ease; }
    .sidebar .nav-btn{ transition:all .15s ease; }
    .sidebar.collapsed .brand-text{ display:none; }
    .sidebar.collapsed .nav-btn{ justify-content:center; gap:0; }
    .sidebar.collapsed .nav-btn span{ display:none; }
    .sidebar.collapsed .foot{ display:none; }
    .sidebar .brand{ display:flex; gap:10px; align-items:center; margin-bottom:24px; }
    .sidebar .brand-icon{ width:36px; height:36px; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.25); }
    .sidebar .nav-btn{ width:100%; display:flex; gap:12px; align-items:center; padding:12px 16px; border:0; border-radius:0.75rem; color:#e9d5ff; background:transparent; text-align:left; transition:.15s ease; }
    /* Quitar decoración de enlaces en el sidebar */
    .sidebar a{ text-decoration:none; }
    .sidebar a:hover{ text-decoration:none; }
    .sidebar .nav-btn.active{ background:rgba(255,255,255,.2); color:#fff; }
    .sidebar .nav-btn:hover{ background:rgba(255,255,255,.1); color:#fff; }
    .sidebar .foot{ margin-top:auto; font-size:.8rem; color:#e9d5ff; opacity:.9; }

    /* Content wrapper */
    .content{ flex:1; display:flex; flex-direction:column; margin-left:var(--sidebar-width); padding-top:var(--header-h); }

    /* Header */
    .app-header{ background:#fff; border-bottom:1px solid #e9ecef; padding:16px 32px; position:fixed; top:0; left:var(--sidebar-width); right:0; height:var(--header-h); z-index:1030; display:flex; align-items:center; }
    .app-header > .d-flex{ width:100%; align-items:center; }
    .app-header .btn{ height:40px; display:inline-flex; align-items:center; }
    .app-header .btn .bi{ line-height:1; }
    .search-wrap input{ height:40px; }
    .app-header .form-check-input{ width:44px; height:24px; margin:0; }
    .search-wrap{ position:relative; max-width:520px; }
    .search-wrap .bi{ position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#6c757d; }
    .search-wrap input{ padding-left:36px; }
    .header-right{ display:flex; align-items:center; gap:12px; }
    .avatar{ width:36px; height:36px; border-radius:50%; background:#f1f3f5; display:inline-flex; align-items:center; justify-content:center; font-weight:600; }

    /* Cards */
    .card-soft{ border-radius: var(--radius-xl); border:1px solid #e9ecef; }
    .icon-bubble{ width:48px; height:48px; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; }

    /* Tables */
    .table> :not(caption)>*>*{ background:transparent; }

    .sidebar.collapsed + .content{ margin-left:72px; }
    .sidebar.collapsed + .content .app-header{ left:72px; }

    .chart-container{ position:relative; height:250px; }
    .chart-container canvas{ width:100% !important; height:100% !important; display:block; }
    

    /* Dark mode */
    [data-bs-theme="dark"] body { background-color: #1e1f25; color: #e2e2e2; }
    [data-bs-theme="dark"] .app-header { background: #252632; border-color: #333842; }
    [data-bs-theme="dark"] .card-soft { background: #2a2b33; border-color: #3b3d47; }
  [data-bs-theme="dark"] .sidebar { background: linear-gradient(180deg, var(--brand), var(--brand-dark)); }
    [data-bs-theme="dark"] .avatar { background: #333842; color: #e2e2e2; }
    [data-bs-theme="dark"] .table> :not(caption)>*>* { color: #e2e2e2; }
    [data-bs-theme="dark"] .text-body-secondary { color: #c0c3c9; }

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
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <div class="brand-icon"><i class="bi bi-heart-fill"></i></div>
        <div class="brand-text">
          <div class="fw-semibold">Laika</div>
          {{-- <div class="small" style="opacity:.85">Centro de atención</div> --}}
        </div>
      </div>
      <nav class="d-grid gap-2">
        <button class="nav-btn"><i class="bi bi-house" data-section="home"></i><span>Inicio</span></button>
        <button class="nav-btn"><i class="bi bi-people" data-section="clientes"></i><span>Clientes</span></button>
        <button class="nav-btn"><i class="bi bi-heart" data-section="mascotas"></i><span>Mascotas</span></button>
        <button class="nav-btn"><i class="bi bi-clipboard-check" data-section="citas"></i><span>Citas</span></button>
        <button class="nav-btn"><i class="bi bi-file-earmark-text" data-section="trabajadores"></i><span>Trabajadores</span></button>
        <button class="nav-btn"><i class="bi bi-graph-up" data-section="reportes"></i><span>Reportes</span></button>
        <a href="/clinicas" class=""><button class="nav-btn"><i class="bi bi-gear" data-section="configuracion"></i><span>Clínicas</span></button></a>
      </nav>
      <div class="foot pt-4">
        <div>Soporte 24/7</div>
        <div>+1 (555) 123-4567</div>
      </div>
    </aside>

    <!-- Content -->
    <div class="content">
      <!-- Header -->
      <header class="app-header">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary" id="btnToggleSidebar" aria-label="Alternar menú"><i class="bi bi-list"></i></button>
            {{-- <div class="search-wrap w-100">
              <i class="bi bi-search"></i>
              <input class="form-control" type="text" placeholder="Buscar mascotas, clientes, citas...">
            </div> --}}
          </div>
          <div class="header-right">
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" role="switch" id="switchTheme">
            </div>
            <button class="btn btn-icon position-relative" aria-label="Notificaciones">
              <i class="bi bi-bell fs-5"></i>
              <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </button>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar"><span>SO</span></div>
              <div>
              @auth
                <div class="small fw-semibold">{{ auth()->user()->nombre ?? auth()->user()->name ?? 'Usuario' }}</div>
              @else
                <div class="small fw-semibold">Invitado</div>
              @endauth

                <div class="small text-body-secondary">Administrador</div>
              </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button class="btn" aria-label="Salir"><i class="bi bi-box-arrow-right text-danger"></i></button>
            </form>
          </div>
        </div>
      </header>

      <!-- Main content -->
      <main class="flex-grow-1 overflow-auto p-4 p-lg-5">
        @yield('content')
      </main>
    </div>
  </div>

      <!-- Modals globales de vistas hijas -->
    <!-- VIEW modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 id="viewModalTitle" class="modal-title">Ver</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="viewModalBody">
            <div class="text-center py-3"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- EDIT modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <form id="editModalForm">
            <div class="modal-header">
              <h5 id="editModalTitle" class="modal-title">Editar</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="editModalBody">
              <div class="text-center py-3"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>

      <!-- CONFIRM DELETE modal -->
      <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirmar eliminación</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="confirmDeleteBody">
              ¿Deseas eliminar este registro?
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
              <button id="confirmDeleteBtn" class="btn btn-danger">Sí, eliminar</button>
            </div>
          </div>
        </div>
      </div>


  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
     <!-- Dashboard JS -->
    <script src="/js/views/dashboard.js"></script>

    <!-- Modals controller -->
    <script src="/js/modals.js"></script>

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
      if(window.innerWidth >= 992 && savedCollapsed){
        sidebar.classList.add('collapsed');
      }
      btn.addEventListener('click', ()=>{
        if(window.innerWidth < 992){
          sidebar.classList.toggle('show');
        } else {
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
        if(window.innerWidth < 992){
          sidebar.classList.remove('collapsed');
        } else {
          sidebar.classList.remove('show');
          const saved = localStorage.getItem('sidebarCollapsed') === 'true';
          sidebar.classList.toggle('collapsed', saved);
        }
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
    document.addEventListener('DOMContentLoaded', () =>{
      setupSidebar();
      setupTheme();
    });
  </script>
 
  @stack('scripts') 
  @yield('scripts')
</body>
</html>
