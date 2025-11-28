@extends('layouts.app_admin')

@section('title', 'VetCare - Panel Recepción')

@section('content')
  <div id="mainContent" data-usuario-nombre="{{ $usuario->nombre }}">
    <div class="container">
      <h3 class="mb-4">Panel Recepción</h3>

      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card card-soft p-3">
            <h5>Crear cliente</h5>
            <form id="formCrearCliente">
              <div class="mb-2">
                <input name="nombre" class="form-control" placeholder="Nombre completo" required>
              </div>
              <div class="mb-2">
                <input name="email" class="form-control" placeholder="Correo electrónico" required>
              </div>
              <div class="mb-2">
                <input name="telefono" class="form-control" placeholder="Teléfono">
              </div>
              <div class="mb-2">
                <input name="password" type="password" class="form-control" placeholder="Contraseña" required>
              </div>
              <button class="btn btn-primary" type="submit">Crear cliente</button>
            </form>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card card-soft p-3">
            <h5>Crear mascota</h5>
            <form id="formCrearMascota">
              <div class="mb-2">
                <select name="user_id" class="form-select" id="selectClientes">
                  <option value="">Seleccionar cliente</option>
                </select>
              </div>
              <div class="mb-2">
                <input name="nombre" class="form-control" placeholder="Nombre mascota" required>
              </div>
              <div class="mb-2">
                <input name="raza" class="form-control" placeholder="Raza">
              </div>
              <button class="btn btn-primary" type="submit">Agregar mascota</button>
            </form>
          </div>
        </div>

        <div class="col-12">
          <div class="card card-soft p-3">
            <h5>Agendar cita</h5>
            <form id="formAgendarCita" class="row g-2">
              <div class="col-md-4">
                <select name="mascota_id" class="form-select" id="selectMascotas"></select>
              </div>
              <div class="col-md-3">
                <select name="servicio_id" class="form-select" id="selectServicios"></select>
              </div>
              <div class="col-md-2">
                <input name="fecha" type="date" class="form-control" required>
              </div>
              <div class="col-md-2">
                <input name="hora" type="time" class="form-control">
              </div>
              <div class="col-md-1">
                <button class="btn btn-success w-100" type="submit">Agendar</button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-12">
          <div class="card card-soft p-3">
            <h5>Próximas citas</h5>
            <div id="listaCitas"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="/js/views/dashboard-recepcionista.js"></script>
@endpush
