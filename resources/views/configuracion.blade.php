<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuración - Laika</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      background: #6f42c1;
      min-height: 100vh;
      color: #fff;
    }
    .sidebar .nav-link {
      color: #fff;
    }
    .sidebar .nav-link.active {
      background: rgba(255,255,255,0.2);
      border-radius: 5px;
    }
    .status {
      padding: 5px 10px;
      border-radius: 15px;
      color: #fff;
      font-size: 0.9rem;
    }
    .status.confirmada {
      background: #28a745;
    }
    .status.por-confirmar {
      background: #ffc107;
      color: #000;
    }
    .status.cancelada {
      background: #dc3545;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 d-none d-md-block sidebar p-3">
      <h4 class="mb-4">Laika</h4>
      <ul class="nav flex-column">
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{route('dashboard')}}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="#"><i class="bi bi-people me-2"></i> Usuarios</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link active" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a>
        </li>
      </ul>
    </nav>

    <!-- Main content -->
    <main class="col-md-10 ms-sm-auto px-4">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <h2>Configuración</h2>
        <div>
          <i class="bi bi-bell me-3 fs-4"></i>
          <span class="badge bg-secondary rounded-circle p-3">CJ</span>
          <span class="ms-2">Administrador</span>
        </div>
      </div>

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
          <!-- Panel: Información de la clínica -->
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
          <!-- Panel: Horario de atención -->
          <div id="panel-horario" class="config-panel card shadow-sm p-4 d-none">
            <h4 class="mb-4">Horario de atención</h4>
            <form id="formHorario">
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead>
                    <tr><th>Día</th><th>Apertura</th><th>Cierre</th><th>Activo</th></tr>
                  </thead>
                  <tbody id="tablaHorario">
                    <!-- Filas generadas por JS -->
                  </tbody>
                </table>
              </div>
              <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Guardar horario</button>
            </form>
          </div>
          <!-- Panel: Notificaciones -->
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
          <!-- Alerta de guardado -->
          <div id="alertPlaceholder" class="mt-3"></div>
        </div>
      </div>
      </div>
    </main>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Lógica de tabs
document.querySelectorAll('#configTabs a').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    // activar link
    document.querySelectorAll('#configTabs a').forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    // mostrar panel
    const target = link.getAttribute('data-target');
    document.querySelectorAll('.config-panel').forEach(p => p.classList.add('d-none'));
    document.getElementById(target).classList.remove('d-none');
  });
});

// Generar filas de horario si no existen
const dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
const tbodyHorario = document.getElementById('tablaHorario');
if (tbodyHorario) {
  dias.forEach(d => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${d}</td>
      <td><input type="time" class="form-control form-control-sm apertura" value="08:00"></td>
      <td><input type="time" class="form-control form-control-sm cierre" value="18:00"></td>
      <td class="text-center"><input type="checkbox" class="form-check-input activo" ${['Sábado','Domingo'].includes(d)?'':'checked'}></td>
    `;
    tbodyHorario.appendChild(tr);
  });
}

// Helpers
function showAlert(message, type='success') {
  const placeholder = document.getElementById('alertPlaceholder');
  if (!placeholder) return;
  placeholder.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
}

// Cargar valores guardados
function loadClinica() {
  const data = JSON.parse(localStorage.getItem('configClinica')||'{}');
  if (!data) return;
  ['Nombre','Direccion','Telefono','Email','Descripcion'].forEach(campo => {
    const el = document.getElementById('clinica'+campo);
    if (el && data[campo.toLowerCase()]) el.value = data[campo.toLowerCase()];
  });
}
function loadNotificaciones() {
  const data = JSON.parse(localStorage.getItem('configNotificaciones')||'{}');
  if (!data) return;
  ['emailReminder','smsReminder','inventoryAlert'].forEach(id => {
    if (data[id] !== undefined) {
      const el = document.getElementById(id);
      if (el) el.checked = data[id];
    }
  });
  if (data.leadTime !== undefined) {
    const lt = document.getElementById('leadTime');
    if (lt) lt.value = data.leadTime;
  }
}
function loadHorario() {
  const data = JSON.parse(localStorage.getItem('configHorario')||'{}');
  if (!Object.keys(data).length) return;
  [...(tbodyHorario?.rows||[])].forEach(row => {
    const dia = row.cells[0].textContent;
    if (data[dia]) {
      row.querySelector('.apertura').value = data[dia].apertura;
      row.querySelector('.cierre').value = data[dia].cierre;
      row.querySelector('.activo').checked = data[dia].activo;
    }
  });
}

// Guardar formularios
document.getElementById('formClinica')?.addEventListener('submit', e => {
  e.preventDefault();
  const payload = {
    nombre: document.getElementById('clinicaNombre').value.trim(),
    direccion: document.getElementById('clinicaDireccion').value.trim(),
    telefono: document.getElementById('clinicaTelefono').value.trim(),
    email: document.getElementById('clinicaEmail').value.trim(),
    descripcion: document.getElementById('clinicaDescripcion').value.trim()
  };
  localStorage.setItem('configClinica', JSON.stringify(payload));
  showAlert('Información de la clínica guardada');
  toggleClinicaEdit(false);
});

document.getElementById('formNotificaciones')?.addEventListener('submit', e => {
  e.preventDefault();
  const payload = {
    emailReminder: document.getElementById('emailReminder').checked,
    smsReminder: document.getElementById('smsReminder').checked,
    inventoryAlert: document.getElementById('inventoryAlert').checked,
    leadTime: parseInt(document.getElementById('leadTime').value || '0',10)
  };
  localStorage.setItem('configNotificaciones', JSON.stringify(payload));
  showAlert('Configuración de notificaciones guardada');
});

document.getElementById('formHorario')?.addEventListener('submit', e => {
  e.preventDefault();
  const data = {};
  [...tbodyHorario.rows].forEach(row => {
    const dia = row.cells[0].textContent;
    data[dia] = {
      apertura: row.querySelector('.apertura').value,
      cierre: row.querySelector('.cierre').value,
      activo: row.querySelector('.activo').checked
    };
  });
  localStorage.setItem('configHorario', JSON.stringify(data));
  showAlert('Horario guardado');
});

// Inicializar
// Establecer valores fijos por defecto si no existen
if (!localStorage.getItem('configClinica')) {
  const defaultClinica = {
    nombre: 'Veterinaria Laika',
    direccion: 'Calle 123 #45-67',
    telefono: '+57 300 000 0000',
    email: 'contacto@laika.com',
    descripcion: 'Clínica veterinaria dedicada al cuidado integral de tus mascotas.'
  };
  localStorage.setItem('configClinica', JSON.stringify(defaultClinica));
}
loadClinica();
loadNotificaciones();
loadHorario();

// ----- Modo edición Información Clínica -----
const camposClinica = ['clinicaNombre','clinicaDireccion','clinicaTelefono','clinicaEmail','clinicaDescripcion'].map(id=>document.getElementById(id));
const btnEditarClinica = document.getElementById('btnEditarClinica');
const btnGuardarClinica = document.getElementById('btnGuardarClinica');
const btnCancelarClinica = document.getElementById('btnCancelarClinica');
let snapshotClinica = null;

function toggleClinicaEdit(edit) {
  camposClinica.forEach(c=>{ if(c){ c.disabled = !edit; }});
  if(edit) {
    btnEditarClinica.classList.add('d-none');
    btnGuardarClinica.classList.remove('d-none');
    btnCancelarClinica.classList.remove('d-none');
  } else {
    btnEditarClinica.classList.remove('d-none');
    btnGuardarClinica.classList.add('d-none');
    btnCancelarClinica.classList.add('d-none');
  }
}

btnEditarClinica?.addEventListener('click', () => {
  // guardar snapshot
  snapshotClinica = camposClinica.reduce((acc,c)=>{ if(c) acc[c.id]=c.value; return acc; },{});
  toggleClinicaEdit(true);
});

btnCancelarClinica?.addEventListener('click', () => {
  if(snapshotClinica) {
    camposClinica.forEach(c=>{ if(c && snapshotClinica[c.id]!==undefined) c.value = snapshotClinica[c.id]; });
  } else {
    loadClinica();
  }
  toggleClinicaEdit(false);
});

// Asegurar modo fijo al inicio
toggleClinicaEdit(false);
</script>
</body>
</html>