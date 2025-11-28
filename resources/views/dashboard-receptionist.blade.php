<x-app-layout>
<div class="container py-3">
  <h4 class="mb-3">Recepción</h4>

  <div class="row g-3">
    <div class="col-12 col-lg-4">
      <div class="card">
        <div class="card-header">Crear cliente</div>
        <div class="card-body">
          <form id="formCrearCliente">
            <div class="mb-2">
              <label class="form-label">Nombre</label>
              <input class="form-control" name="nombre" required />
            </div>
            <div class="mb-2">
              <label class="form-label">Email</label>
              <input class="form-control" name="email" type="email" required />
            </div>
            <div class="mb-2">
              <label class="form-label">Teléfono</label>
              <input class="form-control" name="telefono" />
            </div>
            <button class="btn btn-primary w-100" type="submit">Crear cuenta</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="card mb-3">
        <div class="card-header">Agendar cita</div>
        <div class="card-body">
          <form id="formAgendarCita" class="row g-2">
            <div class="col-12 col-md-6">
              <label class="form-label">Cliente</label>
              <input class="form-control" id="inputCliente" placeholder="Buscar por nombre o email" />
              <input type="hidden" name="user_id" id="user_id" />
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Mascota</label>
              <input class="form-control" id="inputMascota" placeholder="Buscar por nombre" />
              <input type="hidden" name="mascota_id" id="mascota_id" />
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Servicio</label>
              <select class="form-select" name="servicio_id" id="servicio_id"></select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Fecha</label>
              <input class="form-control" name="fecha" type="date" required />
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Hora</label>
              <input class="form-control" name="hora" type="time" />
            </div>
            <div class="col-12">
              <label class="form-label">Nota</label>
              <textarea class="form-control" name="nota" rows="2"></textarea>
            </div>
            <div class="col-12">
              <button class="btn btn-success" type="submit">Agendar</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Citas de hoy</div>
        <div class="card-body">
          <div id="listaCitasHoy"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/js/views/dashboard-receptionist.js"></script>
</x-app-layout>
