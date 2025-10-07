@extends('layouts.app_admin')

{{-- Título de la página --}}

@section('title', 'Configuración')

{{-- Aside específico para esta vista --}}
@section('aside')
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{route('dashboard')}}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="#"><i class="bi bi-people me-2"></i> Usuarios</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link text-white" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a>
        </li>
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
            <div class="list-group" id="configTabs">
              <a href="#" data-target="panel-clinica" class="list-group-item list-group-item-action mb-2"><i class="bi bi-hospital me-2"></i> Información de la clínica</a>
              <a href="#" data-target="panel-horario" class="list-group-item list-group-item-action mb-2"><i class="bi bi-clock me-2"></i> Horario de atención</a>
              <a href="#" data-target="panel-notificaciones" class="list-group-item list-group-item-action active"><i class="bi bi-bell me-2"></i> Notificaciones</a>
            </div>
          </div>
        </div>

        <div class="col-md-8 mb-3">
          <!-- Paneles -->
          <div id="panel-clinica" class="config-panel card shadow-sm p-4 d-none">
            <h4 class="mb-4">Información de la clínica</h4>
            <form id="formClinica">
              <div class="mb-3">
                <label class="form-label" for="clinicaNombre">Nombre de la clínica</label>
                <input class="form-control" id="clinicaNombre" type="text" placeholder="Veterinaria Laika" disabled />
              </div>
              <div class="mb-3">
                <label class="form-label" for="clinicaDireccion">Dirección</label>
                <input class="form-control" id="clinicaDireccion" type="text" placeholder="Calle 123 #45-67" disabled />
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="clinicaTelefono">Teléfono</label>
                  <input class="form-control" id="clinicaTelefono" type="text" placeholder="+57 300 000 0000" disabled />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="clinicaEmail">Email</label>
                  <input class="form-control" id="clinicaEmail" type="email" placeholder="contacto@laika.com" disabled />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="clinicaDescripcion">Descripción</label>
                <textarea class="form-control" id="clinicaDescripcion" rows="3" placeholder="Breve descripción de la clínica" disabled></textarea>
              </div>
              <div class="d-flex gap-2">
                <button type="button" id="btnEditarClinica" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Editar</button>
                <button type="submit" id="btnGuardarClinica" class="btn btn-primary d-none"><i class="bi bi-save me-1"></i>Guardar</button>
                <button type="button" id="btnCancelarClinica" class="btn btn-light border d-none"><i class="bi bi-x-lg me-1"></i>Cancelar</button>
              </div>
            </form>
          </div>

          <div id="panel-horario" class="config-panel card shadow-sm p-4 d-none">
            <h4 class="mb-4">Horario de atención</h4>
            <form id="formHorario">
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead>
                    <tr><th>Día</th><th>Apertura</th><th>Cierre</th><th>Activo</th></tr>
                  </thead>
                  <tbody id="tablaHorario"></tbody>
                </table>
              </div>
              <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Guardar horario</button>
            </form>
          </div>

          <div id="panel-notificaciones" class="config-panel card shadow-sm p-4">
            <h4 class="mb-4">Configuración de notificaciones</h4>
            <form id="formNotificaciones">
              <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                  <strong>Recordatorio de citas por email</strong>
                  <div class="text-muted" style="font-size:0.9em">Enviar recordatorio de citas programadas a los clientes</div>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="emailReminder" checked>
                </div>
              </div>
              <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                  <strong>Recordatorio de citas por SMS</strong>
                  <div class="text-muted" style="font-size:0.9em">Enviar recordatorio de citas por mensaje de texto</div>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="smsReminder" checked>
                </div>
              </div>
              <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                  <strong>Alerta de inventario bajo</strong>
                  <div class="text-muted" style="font-size:0.9em">Notificar cuando los productos estén por agotarse</div>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="inventoryAlert" checked>
                </div>
              </div>
              <hr>
              <div class="mb-3">
                <label for="leadTime" class="form-label">Antelación para recordatorios (horas):</label>
                <input type="number" class="form-control" id="leadTime" placeholder="24">
              </div>
              <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Guardar configuración</button>
            </form>
          </div>

          <div id="alertPlaceholder" class="mt-3"></div>
        </div>
      </div>
      @endsection

@section('scripts')
<script src="{{ asset('js/configuracion_admin.js') }}"></script>
@endsection