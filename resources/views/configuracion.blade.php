<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Configuración - Clínicas | VetCare - Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root{ --bs-body-font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif; --sidebar-width: 260px; --radius-xl: 0.75rem; --header-h: 70px; --brand: #3A7CA5; --brand-dark: #2f6485; --bs-primary: var(--brand); --bs-primary-rgb: 58,124,165; --bs-link-color: var(--brand); --bs-link-hover-color: var(--brand-dark); --bs-primary-bg-subtle: #d7eaf4; --bs-primary-border-subtle: #a7c9dc; --bs-primary-text-emphasis: #1f4f6a; }
    body{ background-color:#f8f9fa; }
    .app{ display:flex; min-height:100vh; overflow:hidden; }
    .sidebar{ width:var(--sidebar-width); background:linear-gradient(180deg,var(--brand),var(--brand-dark)); color:#fff; padding:24px; display:flex; flex-direction:column; position:fixed; top:0; left:0; height:100vh; z-index:1040; }
    .sidebar.collapsed{ width:72px; padding:24px 12px; }
    .sidebar.collapsed .brand{ justify-content:center; }
    .sidebar .nav-btn{ transition:all .15s ease; }
    .sidebar.collapsed .brand-text{ display:none; }
    .sidebar.collapsed .nav-btn{ justify-content:center; gap:0; }
    .sidebar.collapsed .nav-btn span{ display:none; }
    .sidebar.collapsed .foot{ display:none; }
    .sidebar .brand{ display:flex; gap:10px; align-items:center; margin-bottom:24px; }
    .sidebar .brand-icon{ width:36px; height:36px; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.25); }
    .sidebar .nav-btn{ width:100%; display:flex; gap:12px; align-items:center; padding:12px 16px; border:0; border-radius:0.75rem; color:#e9d5ff; background:transparent; text-align:left; text-decoration:none; }
    .sidebar .nav-btn.active{ background:rgba(255,255,255,.2); color:#fff; }
    .sidebar .nav-btn:hover{ background:rgba(255,255,255,.1); color:#fff; }
    .sidebar .foot{ margin-top:auto; font-size:.8rem; color:#e9d5ff; opacity:.9; }
    .content{ flex:1; display:flex; flex-direction:column; margin-left:var(--sidebar-width); padding-top:var(--header-h); }
    .app-header{ background:#fff; border-bottom:1px solid #e9ecef; padding:16px 32px; position:fixed; top:0; left:var(--sidebar-width); right:0; height:var(--header-h); z-index:1030; display:flex; align-items:center; }
    .app-header > .d-flex{ width:100%; align-items:center; }
    .app-header .btn{ height:40px; display:inline-flex; align-items:center; }
    .search-wrap input{ height:40px; }
    .app-header .form-check-input{ width:44px; height:24px; margin:0; }
    .header-right{ display:flex; align-items:center; gap:12px; }
    .avatar{ width:36px; height:36px; border-radius:50%; background:#f1f3f5; display:inline-flex; align-items:center; justify-content:center; font-weight:600; }
    .table>:not(caption)>*>*{ background:transparent; }
    .sidebar.collapsed + .content{ margin-left:72px; }
    .sidebar.collapsed + .content .app-header{ left:72px; }
    [data-bs-theme="dark"] body { background-color:#1e1f25; color:#e2e2e2; }
    [data-bs-theme="dark"] .app-header{ background:#252632; border-color:#333842; }
    [data-bs-theme="dark"] .avatar{ background:#333842; color:#e2e2e2; }
    [data-bs-theme="dark"] .table>:not(caption)>*>* { color:#e2e2e2; }
    [data-bs-theme="dark"] .text-body-secondary{ color:#c0c3c9; }
    @media (max-width:992px){ .sidebar{ inset:0 auto 0 0; transform:translateX(-100%); transition:transform .2s ease; } .sidebar.show{ transform:translateX(0); } .content{ margin-left:0 !important; } .app-header{ left:0 !important; } }
  </style>
  @stack('head')
</head>
<body>
  <div class="app">
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <div class="brand-icon"><i class="bi bi-heart-fill"></i></div>
        <div class="brand-text">
          <div class="fw-semibold">Laika</div>
        </div>
      </div>
      <nav class="d-grid gap-2">
        <a class="nav-btn" href="/dashboard/home"><i class="bi bi-house" data-section="home"></i><span>Home</span></a>
        <a class="nav-btn" href="/dashboard/clientes"><i class="bi bi-people" data-section="clientes"></i><span>Clientes</span></a>
        <a class="nav-btn" href="/dashboard/mascotas"><i class="bi bi-heart" data-section="mascotas"></i><span>Mascotas</span></a>
        <a class="nav-btn" href="/dashboard/citas"><i class="bi bi-clipboard-check" data-section="citas"></i><span>Citas</span></a>
        <a class="nav-btn" href="/dashboard/trabajadores"><i class="bi bi-file-earmark-text" data-section="trabajadores"></i><span>Trabajadores</span></a>
        <a class="nav-btn" href="/dashboard/reportes"><i class="bi bi-graph-up" data-section="reportes"></i><span>Reportes</span></a>
        <a class="nav-btn active" href="/dashboard/configuracion"><i class="bi bi-gear" data-section="configuracion"></i><span>Configuración</span></a>
      </nav>
      <div class="foot pt-4">
        <div>Soporte 24/7</div>
        <div>+1 (555) 123-4567</div>
      </div>
    </aside>
    <div class="content">
      <header class="app-header">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary" id="btnToggleSidebar" aria-label="Alternar menú"><i class="bi bi-list"></i></button>
          </div>
          <div class="header-right">
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" role="switch" id="switchTheme">
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar"><span>{{ substr(($usuario->nombre ?? 'U'),0,2) }}</span></div>
              <div>
                <div class="small fw-semibold">{{ $usuario->nombre ?? 'Usuario' }}</div>
                <div class="small text-body-secondary">Administrador</div>
              </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
              @csrf
              <button class="btn" aria-label="Salir"><i class="bi bi-box-arrow-right text-danger"></i></button>
            </form>
          </div>
        </div>
      </header>
      <main class="flex-grow-1 overflow-auto p-4 p-lg-5">
    <!-- Configuración UI -->
      <div class="row mt-4">
        <div class="col-md-4 mb-3">
          <div class="card shadow-sm p-3">
            <h5 class="mb-4">Configuración</h5>
            @php($currentTab = request('tab','clinica'))
            <div class="list-group" id="configTabs">
              <a href="#" data-target="panel-clinica" class="list-group-item list-group-item-action mb-2 {{ $currentTab=='clinica' ? 'active' : '' }}"><i class="bi bi-hospital me-2"></i> Información de la clínica</a>
              <a href="#" data-target="panel-horario" class="list-group-item list-group-item-action mb-2 {{ $currentTab=='horario' ? 'active' : '' }}"><i class="bi bi-clock me-2"></i> Horario de atención</a>
              <a href="#" data-target="panel-trabajadores" class="list-group-item list-group-item-action mb-2 {{ $currentTab=='trabajadores' ? 'active' : '' }}"><i class="bi bi-people-fill me-2"></i> Trabajadores</a>
            </div>
          </div>
        </div>

        <div class="col-md-8 mb-3">
          <!-- Paneles -->
            <div id="panel-clinica" class="config-panel card shadow-sm p-4 {{ $currentTab=='clinica' ? '' : 'd-none' }}">
            <h4 class="mb-4">Información de la clínica: {{ $clinica->nombre }}</h4>
            @if(session('success') && $currentTab=='clinica')
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error') && $currentTab=='clinica')
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <form id="formClinica" method="POST" action="{{ route('configuracion.clinica.update',$clinica->id) }}">
              @csrf
              @method('PUT')
              <div class="mb-3">
                <label class="form-label" for="clinicaNombre">Nombre de la clínica</label>
                <input class="form-control" id="clinicaNombre" name="nombre" type="text" value="{{ old('nombre',$clinica->nombre) }}" disabled required />
                @error('nombre')<small class="text-danger">{{ $message }}</small>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="clinicaDireccion">Dirección</label>
                <input class="form-control" id="clinicaDireccion" name="direccion" type="text" value="{{ old('direccion',$clinica->direccion) }}" disabled required />
                @error('direccion')<small class="text-danger">{{ $message }}</small>@enderror
                <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="clinicaTelefono">Teléfono</label>
                  <input class="form-control" id="clinicaTelefono" name="telefono" type="text" value="{{ old('telefono',$clinica->telefono) }}" disabled required />
                  @error('telefono')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="clinicaEmail">Email</label>
                  <input class="form-control" id="clinicaEmail" name="email" type="email" value="{{ old('email',$clinica->email) }}" disabled required />
                  @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="clinicaSite">Sitio / URL</label>
                <input class="form-control" id="clinicaSite" name="site" type="text" value="{{ old('site',$clinica->site) }}" disabled />
                @error('site')<small class="text-danger">{{ $message }}</small>@enderror
              </div>
              <div class="mb-3 d-flex gap-4 align-items-center">
                <div class="form-check">
                  <input type="hidden" name="is_open" value="0" />
                  <input class="form-check-input" type="checkbox" id="clinicaOpen" name="is_open" value="1" {{ $clinica->is_open ? 'checked' : '' }} disabled />
                  <label class="form-check-label" for="clinicaOpen">Abierta</label>
                </div>
                <div class="form-check">
                  <input type="hidden" name="is_visible" value="0" />
                  <input class="form-check-input" type="checkbox" id="clinicaVisible" name="is_visible" value="1" {{ $clinica->is_visible ? 'checked' : '' }} disabled />
                  <label class="form-check-label" for="clinicaVisible">Visible</label>
                </div>
              </div>
              <div class="d-flex gap-2">
                <button type="button" id="btnEditarClinica" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Editar</button>
                <button type="submit" id="btnGuardarClinica" class="btn btn-primary d-none"><i class="bi bi-save me-1"></i>Guardar</button>
                <button type="button" id="btnCancelarClinica" class="btn btn-light border d-none"><i class="bi bi-x-lg me-1"></i>Cancelar</button>
              </div>
            </form>
          </div>

          <div id="panel-horario" class="config-panel card shadow-sm p-4 {{ $currentTab=='horario' ? '' : 'd-none' }}">
              <h4 class="mb-4">Horario de atención</h4>
              @if(session('success') && $currentTab=='horario')
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif
              @if(session('error') && $currentTab=='horario')
                <div class="alert alert-danger">{{ session('error') }}</div>
              @endif

              <form id="formHorario" method="POST" action="{{ route('configuracion.horarios.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="clinica_id" value="{{ $clinica->id }}" />

                <div class="table-responsive">
                  <table class="table align-middle">
                    <thead>
                      <tr><th>Día</th><th>Apertura</th><th>Cierre</th><th>Activo</th></tr>
                    </thead>
                    <tbody id="tablaHorario">
                      @if($horarios->isEmpty())
                        <tr><td colspan="4" class="text-center text-muted">No hay horarios configurados.</td></tr>
                      @else
                        @foreach($horarios as $horario)
                          <tr data-id="{{ $horario->id }}">
                            <td style="width:180px;">{{ ucfirst($horario->dia_semana) }}</td>
                            <td style="width:160px;">
                              <input type="time" name="horarios[{{ $horario->id }}][hora_inicio]" class="form-control hora-input" value="{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}" />
                            </td>
                            <td style="width:160px;">
                              <input type="time" name="horarios[{{ $horario->id }}][hora_fin]" class="form-control hora-input" value="{{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}" />
                            </td>
                            <td style="width:80px;">
                              <div class="form-check form-switch">
                                <input class="form-check-input activo-checkbox" type="checkbox" name="horarios[{{ $horario->id }}][activo]" value="1" {{ $horario->activo ? 'checked' : '' }}>
                              </div>
                            </td>
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Guardar horario</button>
              </form>
            </div>




          
          <div id="alertPlaceholder" class="mt-3"></div>
          <div id="panel-trabajadores" class="config-panel card shadow-sm p-4 {{ $currentTab=='trabajadores' ? '' : 'd-none' }}">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="mb-0">Trabajadores de la clínica</h4>
              <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAsignarTrabajador">
                <i class="bi bi-person-plus-fill me-1"></i> Asignar existente
              </button>
            </div>
            @if(session('success') && $currentTab=='trabajadores')
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error') && $currentTab=='trabajadores')
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($trabajadores->isEmpty())
              <p class="text-muted">No hay trabajadores asociados a esta clínica.</p>
            @else
            <div class="table-responsive mb-4">
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Email</th>
                    <th>Activo</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($trabajadores as $t)
                    <tr>
                      <td>{{ $t->nombre }} {{ $t->apellido_paterno }}</td>
                      <td>
                        @php($rolesMap = ['A'=>'Admin','R'=>'Recepción','V'=>'Veterinario'])
                        <span class="badge bg-secondary">{{ $rolesMap[$t->rol] ?? $t->rol }}</span>
                      </td>
                      <td>{{ $t->email }}</td>
                      <td>{!! $t->is_active ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>' !!}</td>
                      <td class="text-end">
                        <form method="POST" action="{{ route('configuracion.clinica.trabajadores.remover', ['clinica'=>$clinica->id,'user'=>$t->id]) }}" onsubmit="return confirm('¿Remover este trabajador de la clínica?');" class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @endif
          </div>

          <!-- Modal Asignar Trabajador Existente -->
          <div class="modal fade" id="modalAsignarTrabajador" tabindex="-1" aria-labelledby="modalAsignarTrabajadorLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalAsignarTrabajadorLabel">Asignar trabajador existente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body">
                    <form method="GET" action="{{ route('configuracion.clinica', $clinica->id) }}" class="row g-2 align-items-end mb-3">
                      <div class="col-md-8">
                        <label for="searchEmail" class="form-label mb-1">Buscar por correo</label>
                        <input type="text" id="searchEmail" name="search_email" class="form-control" placeholder="usuario@correo.com" value="{{ request('search_email') }}">
                        <input type="hidden" name="tab" value="trabajadores">
                        <input type="hidden" name="open_modal" value="asignar">
                      </div>
                      <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Buscar</button>
                        <a href="{{ route('configuracion.clinica', ['clinica'=>$clinica->id, 'tab'=>'trabajadores', 'open_modal'=>'asignar']) }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                      </div>
                    </form>
                    @if($availableTrabajadores->isEmpty())
                      <p class="text-muted">No hay trabajadores (Administrador/Veterinario) disponibles para asignar.</p>
                    @else
                      <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                          <thead class="table-light">
                            <tr>
                              <th>Nombre</th>
                              <th>Rol</th>
                              <th>Email</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($availableTrabajadores as $u)
                              <tr>
                                <td>{{ $u->nombre }} {{ $u->apellido_paterno }}</td>
                                <td>
                                  @php($rolesMap = ['A'=>'Admin','V'=>'Veterinario'])
                                  <span class="badge bg-secondary">{{ $rolesMap[$u->rol] ?? $u->rol }}</span>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td class="text-end">
                                  <form method="POST" action="{{ route('configuracion.clinica.trabajadores.asignar', $clinica->id) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $u->id }}" />
                                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i> Asignar</button>
                                  </form>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', () => {
  // ===== Toggle tabs =====
  const tabLinks = document.querySelectorAll('#configTabs a');
  const panels = document.querySelectorAll('.config-panel');
  tabLinks.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const target = link.dataset.target;
      // Ocultar todas
      panels.forEach(p => p.classList.add('d-none'));
      // Mostrar la seleccionada si existe
      const panel = document.getElementById(target);
      if (panel) panel.classList.remove('d-none');
      // Active en link
      tabLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      // Actualizar la URL para persistir pestaña
      const url = new URL(window.location.href);
      url.searchParams.set('tab', target.replace('panel-',''));
      window.history.replaceState({}, '', url.toString());
    });
  });

  // ===== Clinic edit toggle =====
  const editBtn = document.getElementById('btnEditarClinica');
  const saveBtn = document.getElementById('btnGuardarClinica');
  const cancelBtn = document.getElementById('btnCancelarClinica');
  const form = document.getElementById('formClinica');
  const inputs = form ? form.querySelectorAll('input:not([type=hidden]), textarea') : [];

  function setEditable(editing){
    inputs.forEach(i => { i.disabled = !editing; });
    if (editBtn) editBtn.classList.toggle('d-none', editing);
    if (saveBtn) saveBtn.classList.toggle('d-none', !editing);
    if (cancelBtn) cancelBtn.classList.toggle('d-none', !editing);
  }

  editBtn?.addEventListener('click', () => setEditable(true));
  cancelBtn?.addEventListener('click', () => {
    window.location.reload();
  });

  // Auto abrir modal de asignar si query open_modal=asignar
  const params = new URLSearchParams(window.location.search);
  if(params.get('open_modal') === 'asignar'){
      const modalEl = document.getElementById('modalAsignarTrabajador');
      if(modalEl){
          const modal = new bootstrap.Modal(modalEl);
          modal.show();
      }
  }
});

        function setupSidebar(){ const btn = document.getElementById('btnToggleSidebar'); const sidebar = document.getElementById('sidebar'); if(!btn || !sidebar) return; const savedCollapsed = localStorage.getItem('sidebarCollapsed') === 'true'; if(window.innerWidth >= 992 && savedCollapsed){ sidebar.classList.add('collapsed'); } btn.addEventListener('click', ()=>{ if(window.innerWidth < 992){ sidebar.classList.toggle('show'); } else { sidebar.classList.toggle('collapsed'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); } }); document.addEventListener('click', (e)=>{ if(window.innerWidth >= 992) return; if(!sidebar.classList.contains('show')) return; const clickInside = sidebar.contains(e.target) || btn.contains(e.target); if(!clickInside) sidebar.classList.remove('show'); }); window.addEventListener('resize', ()=>{ if(window.innerWidth < 992){ sidebar.classList.remove('collapsed'); } else { sidebar.classList.remove('show'); const saved = localStorage.getItem('sidebarCollapsed') === 'true'; sidebar.classList.toggle('collapsed', saved); } }); }
        function setupTheme(){ const switchEl = document.getElementById('switchTheme'); const saved = localStorage.getItem('theme') || 'light'; document.documentElement.setAttribute('data-bs-theme', saved); if(switchEl) switchEl.checked = saved === 'dark'; switchEl?.addEventListener('change', ()=>{ const next = switchEl.checked ? 'dark' : 'light'; document.documentElement.setAttribute('data-bs-theme', next); localStorage.setItem('theme', next); }); }
        document.addEventListener('DOMContentLoaded', ()=>{ setupSidebar(); setupTheme(); });
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>