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
                <h4 class="mb-4">Seleccionar Clínica</h4>
                <div class="card shadow-sm mt-2">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Clínicas</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaClinica">
                            <i class="bi bi-plus-lg me-1"></i> Nueva clínica
                        </button>
                    </div>
                    @if(session('success'))
                        <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mx-3 mt-3 mb-0">{{ session('error') }}</div>
                    @endif
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clinicas as $clinica)
                                        <tr>
                                            <td>{{ $clinica->nombre }}</td>
                                            <td class="text-muted" style="max-width:220px">{{ Str::limit($clinica->direccion, 40) }}</td>
                                            <td>{{ $clinica->telefono ?? '-' }}</td>
                                            <td>{{ $clinica->email ?? '-' }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('configuracion.clinica', $clinica->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil-square me-1"></i> Configurar
                                                </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary ms-1 btn-servicios" data-bs-toggle="modal" data-bs-target="#modalServiciosClinica"
                                                    data-clinica-id="{{ $clinica->id }}" data-clinica-nombre="{{ $clinica->nombre }}">
                                                <i class="bi bi-list-check me-1"></i> Servicios
                                            </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No hay clínicas registradas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Nueva Clínica -->
    <div class="modal fade" id="modalNuevaClinica" tabindex="-1" aria-labelledby="modalNuevaClinicaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevaClinicaLabel">Registrar nueva clínica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form method="POST" action="{{ route('configuracion.clinica.store') }}" id="formNuevaClinica">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="ncNombre">Nombre</label>
                                <input type="text" name="nombre" id="ncNombre" class="form-control" required maxlength="100" value="{{ old('nombre') }}">
                                @error('nombre')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="ncTelefono">Teléfono</label>
                                <input type="text" name="telefono" id="ncTelefono" class="form-control" required maxlength="15" value="{{ old('telefono') }}">
                                @error('telefono')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="ncDireccion">Dirección</label>
                                <input type="text" name="direccion" id="ncDireccion" class="form-control" required maxlength="255" value="{{ old('direccion') }}">
                                @error('direccion')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="ncEmail">Email</label>
                                <input type="email" name="email" id="ncEmail" class="form-control" required maxlength="150" value="{{ old('email') }}">
                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="ncSite">Sitio / URL</label>
                                <input type="text" name="site" id="ncSite" class="form-control" maxlength="255" value="{{ old('site') }}">
                                @error('site')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="ncOpen" name="is_open" value="1" checked>
                                    <label class="form-check-label" for="ncOpen">Abierta</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="ncVisible" name="is_visible" value="1" checked>
                                    <label class="form-check-label" for="ncVisible">Visible</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Asignar Servicios a Clínica -->
    <div class="modal fade" id="modalServiciosClinica" tabindex="-1" aria-labelledby="modalServiciosClinicaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalServiciosClinicaLabel">Agregar servicios a <span id="svcClinicaNombre"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formServiciosClinica" method="POST" action="#">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted">Selecciona los servicios que quieres agregar a la clínica.</p>
                        <div class="d-grid gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Consulta médica" id="svc1" name="servicios[]">
                                <label class="form-check-label" for="svc1">Consulta médica</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Corte de pelo" id="svc2" name="servicios[]">
                                <label class="form-check-label" for="svc2">Corte de pelo</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Baño" id="svc3" name="servicios[]">
                                <label class="form-check-label" for="svc3">Baño</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Vacunación" id="svc4" name="servicios[]">
                                <label class="form-check-label" for="svc4">Vacunación</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Desparasitación" id="svc5" name="servicios[]">
                                <label class="form-check-label" for="svc5">Desparasitación</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Limpieza dental" id="svc6" name="servicios[]">
                                <label class="form-check-label" for="svc6">Limpieza dental</label>
                            </div>
                        </div>
                        <hr class="my-3">
                        <h6 class="mb-2">Servicios actuales</h6>
                        <div id="svcListWrap" class="table-responsive border rounded">
                            <table class="table table-sm align-middle mb-0" id="svcTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:35%">Nombre</th>
                                        <th style="width:20%">Precio</th>
                                        <th style="width:20%">Tiempo (min)</th>
                                        <th class="text-end" style="width:25%"></th>
                                    </tr>
                                </thead>
                                <tbody id="svcTbody">
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin servicios</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setupSidebar(){ const btn = document.getElementById('btnToggleSidebar'); const sidebar = document.getElementById('sidebar'); if(!btn || !sidebar) return; const savedCollapsed = localStorage.getItem('sidebarCollapsed') === 'true'; if(window.innerWidth >= 992 && savedCollapsed){ sidebar.classList.add('collapsed'); } btn.addEventListener('click', ()=>{ if(window.innerWidth < 992){ sidebar.classList.toggle('show'); } else { sidebar.classList.toggle('collapsed'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); } }); document.addEventListener('click', (e)=>{ if(window.innerWidth >= 992) return; if(!sidebar.classList.contains('show')) return; const clickInside = sidebar.contains(e.target) || btn.contains(e.target); if(!clickInside) sidebar.classList.remove('show'); }); window.addEventListener('resize', ()=>{ if(window.innerWidth < 992){ sidebar.classList.remove('collapsed'); } else { sidebar.classList.remove('show'); const saved = localStorage.getItem('sidebarCollapsed') === 'true'; sidebar.classList.toggle('collapsed', saved); } }); }
        function setupTheme(){ const switchEl = document.getElementById('switchTheme'); const saved = localStorage.getItem('theme') || 'light'; document.documentElement.setAttribute('data-bs-theme', saved); if(switchEl) switchEl.checked = saved === 'dark'; switchEl?.addEventListener('change', ()=>{ const next = switchEl.checked ? 'dark' : 'light'; document.documentElement.setAttribute('data-bs-theme', next); localStorage.setItem('theme', next); }); }
        document.addEventListener('DOMContentLoaded', ()=>{ 
            setupSidebar(); setupTheme(); 
            // preparar modal de servicios
            const modalEl = document.getElementById('modalServiciosClinica');
            const form = document.getElementById('formServiciosClinica');
            const titleSpan = document.getElementById('svcClinicaNombre');
            const base = '{{ url('/configuracion/clinica') }}';
            const svcTbody = document.getElementById('svcTbody');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let currentClinicaId = null;
            let existingByName = new Map(); // nombre -> servicio

            function getPresetNames(){
                return Array.from(form.querySelectorAll('input[name="servicios[]"]')).map(i=> i.value.trim());
            }

            function syncPresetChecks(){
                const inputs = form.querySelectorAll('input[name="servicios[]"]');
                inputs.forEach(inp => {
                    const val = (inp.value || '').trim();
                    inp.checked = existingByName.has(val);
                });
            }

            function renderServicios(rows){
                if(!svcTbody) return;
                svcTbody.innerHTML = '';
                existingByName = new Map();
                if(Array.isArray(rows)){
                    for(const s of rows){ existingByName.set((s.nombre||'').trim(), s); }
                }
                if(!rows || rows.length === 0){
                    svcTbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin servicios</td></tr>';
                    syncPresetChecks();
                    return;
                }
                for(const s of rows){
                    const tr = document.createElement('tr');
                    tr.dataset.id = s.id;
                    tr.innerHTML = `
                        <td>
                            <span class="v-name">${s.nombre ?? ''}</span>
                            <input class="form-control form-control-sm d-none e-name" value="${s.nombre ?? ''}">
                        </td>
                        <td>
                            <span class="v-precio">${Number(s.precio).toFixed(2)}</span>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm d-none e-precio" value="${s.precio}">
                        </td>
                        <td>
                            <span class="v-tiempo">${s.tiempo_estimado}</span>
                            <input type="number" min="1" class="form-control form-control-sm d-none e-tiempo" value="${s.tiempo_estimado}">
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit">Editar</button>
                            <button type="button" class="btn btn-sm btn-primary d-none btn-save">Guardar</button>
                            <button type="button" class="btn btn-sm btn-secondary d-none btn-cancel">Cancelar</button>
                        </td>`;
                    svcTbody.appendChild(tr);
                }
                // marcar checkboxes de presets que ya existen
                syncPresetChecks();
            }

            function bindRowActions(clinicaId){
                if(!svcTbody) return;
                svcTbody.addEventListener('click', async (ev)=>{
                    const btn = ev.target.closest('button');
                    if(!btn) return;
                    const tr = btn.closest('tr');
                    const id = tr?.dataset.id;
                    if(btn.classList.contains('btn-edit')){
                        tr.querySelectorAll('.v-name,.v-precio,.v-tiempo').forEach(el=> el.classList.add('d-none'));
                        tr.querySelectorAll('.e-name,.e-precio,.e-tiempo').forEach(el=> el.classList.remove('d-none'));
                        tr.querySelector('.btn-edit').classList.add('d-none');
                        tr.querySelector('.btn-save').classList.remove('d-none');
                        tr.querySelector('.btn-cancel').classList.remove('d-none');
                    } else if(btn.classList.contains('btn-cancel')){
                        tr.querySelectorAll('.e-name,.e-precio,.e-tiempo').forEach(el=> el.classList.add('d-none'));
                        tr.querySelectorAll('.v-name,.v-precio,.v-tiempo').forEach(el=> el.classList.remove('d-none'));
                        tr.querySelector('.btn-edit').classList.remove('d-none');
                        tr.querySelector('.btn-save').classList.add('d-none');
                        tr.querySelector('.btn-cancel').classList.add('d-none');
                    } else if(btn.classList.contains('btn-save')){
                        const nombre = tr.querySelector('.e-name').value.trim();
                        const precio = tr.querySelector('.e-precio').value;
                        const tiempo = tr.querySelector('.e-tiempo').value;
                        try{
                            const resp = await fetch(`{{ url('/configuracion/servicio') }}/${id}`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrf,
                                },
                                body: JSON.stringify({ nombre, precio, tiempo_estimado: parseInt(tiempo,10) }),
                            });
                            if(!resp.ok){ throw new Error('Error al guardar'); }
                            const data = await resp.json();
                            const s = data.servicio;
                            tr.querySelector('.v-name').textContent = s.nombre;
                            tr.querySelector('.v-precio').textContent = Number(s.precio).toFixed(2);
                            tr.querySelector('.v-tiempo').textContent = s.tiempo_estimado;
                            tr.querySelector('.e-name').value = s.nombre;
                            tr.querySelector('.e-precio').value = s.precio;
                            tr.querySelector('.e-tiempo').value = s.tiempo_estimado;
                            // toggle back
                            tr.querySelectorAll('.e-name,.e-precio,.e-tiempo').forEach(el=> el.classList.add('d-none'));
                            tr.querySelectorAll('.v-name,.v-precio,.v-tiempo').forEach(el=> el.classList.remove('d-none'));
                            tr.querySelector('.btn-edit').classList.remove('d-none');
                            tr.querySelector('.btn-save').classList.add('d-none');
                            tr.querySelector('.btn-cancel').classList.add('d-none');
                        }catch(e){
                            alert('No se pudo guardar el servicio.');
                        }
                    }
                }, { passive: true });
            }
            modalEl?.addEventListener('show.bs.modal', async (ev)=>{
                const btn = ev.relatedTarget;
                const id = btn?.getAttribute('data-clinica-id');
                const nombre = btn?.getAttribute('data-clinica-nombre') || '';
                titleSpan.textContent = nombre;
                // reset checks
                form.querySelectorAll('input[type="checkbox"]').forEach(ch=> ch.checked = false);
                // set action
                form.setAttribute('action', `${base}/${id}/servicios`);
                currentClinicaId = id;
                // cargar servicios actuales
                try{
                    const resp = await fetch(`${base}/${id}/servicios`, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
                    const data = await resp.json();
                    renderServicios(data.servicios || []);
                    bindRowActions(id);
                }catch(e){
                    renderServicios([]);
                }
            });

            // Enviar: agrega los seleccionados que no existen y elimina los que existen pero se desmarcaron
            form?.addEventListener('submit', async (ev)=>{
                if(!currentClinicaId) return; // seguridad
                ev.preventDefault();
                const presetNames = getPresetNames();
                const selected = new Set(Array.from(form.querySelectorAll('input[name="servicios[]"]:checked')).map(i=> i.value.trim()));
                const existingNames = Array.from(existingByName.keys());

                const toAdd = Array.from(selected).filter(n => !existingByName.has(n));
                const toRemove = existingNames.filter(n => !selected.has(n) && presetNames.includes(n));

                try{
                    // Agregar en lote si hay
                    if(toAdd.length){
                        const fd = new FormData();
                        toAdd.forEach(n => fd.append('servicios[]', n));
                        const respAdd = await fetch(`${base}/${currentClinicaId}/servicios`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf },
                            body: fd,
                        });
                        // no usamos respuesta (puede redirigir), continuamos
                    }

                    // Eliminar en paralelo
                    if(toRemove.length){
                        const deletes = toRemove.map(n => {
                            const s = existingByName.get(n);
                            return fetch(`{{ url('/configuracion/servicio') }}/${s.id}`, { method:'DELETE', headers:{ 'X-CSRF-TOKEN': csrf, 'X-Requested-With':'XMLHttpRequest' } });
                        });
                        await Promise.allSettled(deletes);
                    }

                    // refrescar lista y checks
                    const resp = await fetch(`${base}/${currentClinicaId}/servicios`, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
                    const data = await resp.json();
                    renderServicios(data.servicios || []);
                }catch(e){
                    alert('No se pudieron aplicar los cambios de servicios.');
                }
            });
        });
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
