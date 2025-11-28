document.addEventListener('DOMContentLoaded', () => {
  cargarServiciosClinica();
  cargarCitasHoy();
  wireForms();
});

function wireForms() {
  const formCliente = document.getElementById('formCrearCliente');
  const formCita = document.getElementById('formAgendarCita');

  if (formCliente) {
    formCliente.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(formCliente));
      const res = await fetch('/recepcion/clientes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(data),
      });
      const json = await res.json();
      if (json.success) {
        alert('Cliente creado');
        document.getElementById('user_id').value = json.user.id;
      } else {
        alert('Error al crear cliente');
      }
    });
  }

  if (formCita) {
    formCita.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(formCita));
      const res = await fetch('/recepcion/citas', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(data),
      });
      const json = await res.json();
      if (json.success) {
        alert('Cita agendada');
        cargarCitasHoy();
      } else {
        alert('Error al agendar cita');
      }
    });
  }

  const inputCliente = document.getElementById('inputCliente');
  const inputMascota = document.getElementById('inputMascota');
  if (inputCliente) {
    inputCliente.addEventListener('input', debounce(async () => {
      const q = inputCliente.value.trim();
      if (!q) return;
      const res = await fetch(`/recepcion/buscar?q=${encodeURIComponent(q)}`);
      const json = await res.json();
      if (json.success && json.users?.length) {
        const u = json.users[0];
        document.getElementById('user_id').value = u.id;
      }
    }, 300));
  }
  if (inputMascota) {
    inputMascota.addEventListener('input', debounce(async () => {
      const q = inputMascota.value.trim();
      if (!q) return;
      const res = await fetch(`/recepcion/buscar?q=${encodeURIComponent(q)}`);
      const json = await res.json();
      if (json.success && json.mascotas?.length) {
        const m = json.mascotas[0];
        document.getElementById('mascota_id').value = m.id;
        if (!document.getElementById('user_id').value && m.user_id) {
          document.getElementById('user_id').value = m.user_id;
        }
      }
    }, 300));
  }
}

async function cargarServiciosClinica() {
  const sel = document.getElementById('servicio_id');
  if (!sel) return;
  try {
    // Reutilizar endpoint existente de configuración si fuera necesario,
    // aquí simplificamos: cargar todos servicios por clínica en el servidor.
    // Fallback: llenar vacio y permitir seleccionar más tarde.
    const res = await fetch('/configuracion/clinica/' + (window.CLINICA_ID || '') + '/servicios');
    const json = await res.json();
    sel.innerHTML = '';
    (json || []).forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id || s.servicio_id || '';
      opt.textContent = s.nombre || s.servicio?.nombre || 'Servicio';
      sel.appendChild(opt);
    });
  } catch (e) {
    // Si falla, mantener select vacío
  }
}

async function cargarCitasHoy() {
  const cont = document.getElementById('listaCitasHoy');
  if (!cont) return;
  cont.innerHTML = '<div class="text-body-secondary">Cargando...</div>';

  try {
    // Usar endpoint de admin citas.json filtrado por hoy si existe.
    const res = await fetch('/citas/json');
    const json = await res.json();
    const citas = json?.data || json?.citas || [];
    cont.innerHTML = '';
    if (!citas.length) {
      cont.innerHTML = '<div class="text-body-secondary">Sin citas</div>';
      return;
    }

    citas.forEach(c => {
      const row = document.createElement('div');
      row.className = 'd-flex justify-content-between align-items-center border-bottom py-2';
      row.innerHTML = `
        <div>
          <div><strong>${c.mascota?.nombre || 'Mascota'}</strong> - ${c.servicio?.nombre || 'Servicio'}</div>
          <div class="small text-body-secondary">${c.fecha || ''} ${c.hora || ''} | ${c.status || ''}</div>
        </div>
        <div>
          <button class="btn btn-sm btn-outline-success" data-id="${c.id}">Completar</button>
        </div>
      `;
      row.querySelector('button').addEventListener('click', async () => {
        const res2 = await fetch(`/recepcion/citas/${c.id}/completar`, { method: 'POST' });
        const j2 = await res2.json();
        if (j2.success) {
          row.querySelector('.small').textContent = (c.fecha || '') + ' ' + (c.hora || '') + ' | completada';
        }
      });
      cont.appendChild(row);
    });
  } catch (e) {
    cont.innerHTML = '<div class="text-danger">Error al cargar citas</div>';
  }
}

function debounce(fn, ms) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}
