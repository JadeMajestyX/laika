@extends('layouts.app_admin')

{{-- Título de la página --}}

@section('title', 'Configuración')

{{-- Aside específico para esta vista --}}
@section('aside')
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{route('dashboard')}}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a>
        </li>
                <li class="nav-item mb-2"><a class="nav-link text-white " href="{{ route('citas') }}"><i class="bi bi-calendar-event me-2"></i> Citas</a></li>
        {{-- <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a>
        </li> --}}
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white bg-white bg-opacity-10 rounded active" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a>
        </li>
@endsection


  @section('header-title', 'Configuración')

  @section('content')
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
      @endsection

@section('scripts')
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
</script>
@endsection