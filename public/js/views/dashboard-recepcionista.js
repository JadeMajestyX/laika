document.addEventListener('DOMContentLoaded', () => {
  cargarClientes();
  cargarServicios();
  cargarCitas();

  document.getElementById('formCrearCliente')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const payload = Object.fromEntries(data.entries());
    try {
      const res = await fetch('/recepcion-dashboard/cliente', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      if (json.success) {
        alert('Cliente creado');
        form.reset();
        cargarClientes();
      } else {
        alert(json.error || 'Error');
      }
    } catch (err) {
      console.error(err);
      alert('Error creando cliente');
    }
  });

  document.getElementById('formCrearMascota')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const payload = Object.fromEntries(data.entries());
    try {
      const res = await fetch('/recepcion-dashboard/mascota', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      if (json.success) {
        alert('Mascota creada');
        form.reset();
        cargarMascotas(payload.user_id);
      } else {
        alert(json.error || 'Error');
      }
    } catch (err) {
      console.error(err);
      alert('Error creando mascota');
    }
  });

  document.getElementById('formAgendarCita')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const payload = Object.fromEntries(data.entries());
    try {
      const res = await fetch('/recepcion-dashboard/agendar', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      if (json.success) {
        alert('Cita agendada');
        form.reset();
        cargarCitas();
      } else {
        alert(json.error || 'Error');
      }
    } catch (err) {
      console.error(err);
      alert('Error agendando cita');
    }
  });
});

async function cargarClientes(){
  try{
    const res = await fetch('/usuarios/json');
    const json = await res.json();
    const select = document.getElementById('selectClientes');
    if(!select) return;
    select.innerHTML = '<option value="">Seleccionar cliente</option>';
    (json.data || json || []).forEach(u => {
      const opt = document.createElement('option');
      opt.value = u.id;
      opt.textContent = u.nombre + (u.email ? ' <' + u.email + '>' : '');
      select.appendChild(opt);
    });
  }catch(err){ console.error(err); }
}

async function cargarMascotas(userId){
  try{
    const url = userId ? `/mascotas/json?user_id=${userId}` : '/mascotas/json';
    const res = await fetch(url);
    const json = await res.json();
    const select = document.getElementById('selectMascotas');
    if(!select) return;
    select.innerHTML = '';
    (json.data || json || []).forEach(m => {
      const opt = document.createElement('option');
      opt.value = m.id;
      opt.textContent = m.nombre + (m.raza ? ' - ' + m.raza : '');
      select.appendChild(opt);
    });
  }catch(err){ console.error(err); }
}

async function cargarServicios(){
  try{
    const res = await fetch('/configuracion/clinica/1/servicios');
    const json = await res.json();
    const select = document.getElementById('selectServicios');
    if(!select) return;
    select.innerHTML = '';
    (json || []).forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id;
      opt.textContent = s.nombre;
      select.appendChild(opt);
    });
  }catch(err){ console.error(err); }
}

async function cargarCitas(){
  try{
    const res = await fetch('/citas/json');
    const json = await res.json();
    const container = document.getElementById('listaCitas');
    if(!container) return;
    container.innerHTML = '';
    (json.data || json || []).forEach(c => {
      const div = document.createElement('div');
      div.className = 'd-flex align-items-center justify-content-between border-bottom py-2';
      div.innerHTML = `<div><strong>${c.mascota?.nombre || '—'}</strong> — ${c.servicio?.nombre || c.servicio_nombre || '—'}<div class="small text-body-secondary">${c.fecha} ${c.hora || ''}</div></div>`;
      const btn = document.createElement('button');
      btn.className = 'btn btn-sm btn-outline-success';
      btn.textContent = c.status === 'completada' ? 'Completada' : 'Marcar completada';
      btn.disabled = c.status === 'completada';
      btn.addEventListener('click', async ()=>{
        try{
          const r = await fetch('/recepcion-dashboard/marcar-completada', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json' }, body: JSON.stringify({ cita_id: c.id }) });
          const jj = await r.json();
          if(jj.success){ cargarCitas(); }
        }catch(err){ console.error(err); }
      });
      div.appendChild(btn);
      container.appendChild(div);
    });
  }catch(err){ console.error(err); }
}
