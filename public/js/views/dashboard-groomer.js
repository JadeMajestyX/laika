// public/js/views/dashboard-groomer.js

let chartInstance = null;

function isGroomingServiceName(name) {
  if (!name) return false;
  const n = String(name).toLowerCase();
  return [
    'Corte de pelo', 'Baño', 'limpieza dental', 'spa', 'uñas', 'desparasitación',
    'peluquer', 'groom', 'aseo'
  ].some((kw) => n.includes(kw));
}

function buildChartSeriesFromCitas(citas, groomerId, serviciosClinica, clinicaId) {
  const diasOrdenados = [
    { en: 'Monday', es: 'Lun' },
    { en: 'Tuesday', es: 'Mar' },
    { en: 'Wednesday', es: 'Mié' },
    { en: 'Thursday', es: 'Jue' },
    { en: 'Friday', es: 'Vie' },
    { en: 'Saturday', es: 'Sáb' },
    { en: 'Sunday', es: 'Dom' },
  ];
  const map = {};
  const toNum = (v) => v == null ? null : Number(v);
  // Limitar solo a la semana actual y a la clínica del usuario, sin filtrar por dueño ni por grooming
  const startOfWeek = (() => {
    const d = new Date();
    const day = d.getDay(); // 0=Dom,1=Lun
    const diffToMonday = (day === 0 ? -6 : 1 - day); // mover al lunes
    const monday = new Date(d);
    monday.setDate(d.getDate() + diffToMonday);
    monday.setHours(0,0,0,0);
    return monday;
  })();
  const endOfWeek = (() => {
    const e = new Date(startOfWeek);
    e.setDate(startOfWeek.getDate() + 7);
    e.setHours(0,0,0,0);
    return e;
  })();
  const inCurrentWeek = (dt) => dt && dt >= startOfWeek && dt < endOfWeek;
  const allowed = new Set(['limpieza dental','corte de pelo','baño']);
  const isAllowedService = (c) => {
    const name = c?.servicio?.nombre ? String(c.servicio.nombre).toLowerCase().trim() : '';
    return allowed.has(name);
  };
  (citas || [])
    .filter((c) => {
      const d = c.fecha ? new Date(c.fecha) : null;
      // si viene clinicaId en el payload, asumir que citasClinica ya son de esa clínica
      return inCurrentWeek(d) && isAllowedService(c);
    })
    .forEach((c) => {
      const d = c.fecha ? new Date(c.fecha) : null;
      if (!d) return;
      const enDay = d.toLocaleDateString('en-US', { weekday: 'long' });
      map[enDay] = (map[enDay] || 0) + 1;
    });
  return {
    labels: diasOrdenados.map((d) => d.es),
    data: diasOrdenados.map((d) => map[d.en] || 0),
  };
}

function renderChart(labels, data) {
  const ctx = document.getElementById('appointmentsChart');
  if (!ctx || typeof Chart === 'undefined') return;
  if (chartInstance) chartInstance.destroy();
  const themeRoot = document.querySelector('[data-bs-theme]') || document.documentElement;
  const styles = getComputedStyle(themeRoot);
  const gridColor = styles.getPropertyValue('--bs-border-color').trim() || '#e9ecef';
  const barColor = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#B35C9C';
  const textColor = (window.getTextColor && window.getTextColor()) || '#6c757d';
  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Citas', data, backgroundColor: barColor, borderRadius: 8 }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor } },
        y: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true },
      },
      plugins: { legend: { display: false } },
    },
  });
}

function updateDashboardMetricsFromCitas(payload) {
  const { citasClinica = [], userId } = payload || {};
  const { serviciosClinica = [], clinicaId } = payload || {};
  const today = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  const isSameDay = (dt) => dt && dt.getFullYear() === today.getFullYear() && dt.getMonth() === today.getMonth() && dt.getDate() === today.getDate();
  const toNum = (v) => v == null ? null : Number(v);
  // Aplicar filtros de servicios permitidos: limpieza dental, corte de pelo, baño
  const allowed = new Set(['limpieza dental','corte de pelo','baño']);
  const isAllowedService = (c) => {
    const name = c?.servicio?.nombre ? String(c.servicio.nombre).toLowerCase().trim() : '';
    return allowed.has(name);
  };
  const citasHoy = citasClinica.filter((c) => {
    const d = c.fecha ? new Date(c.fecha) : null;
    return isSameDay(d) && isAllowedService(c);
  });
  const completadas = citasHoy.filter((c) => c.status === 'completada');
  const serviciosRealizados = citasHoy.filter((c) => isAllowedService(c));
  const mascotasSet = new Set();
  citasHoy.forEach((c) => { if (c.mascota_id) mascotasSet.add(c.mascota_id); });
  const mascotasAtendidas = mascotasSet.size;

  const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  const setPct = (id, pct) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = (pct >= 0 ? '+' : '') + pct + '%';
    el.className = 'small ' + (pct >= 0 ? 'text-success' : 'text-danger');
  };
  setVal('citasHoy', citasHoy.length);
  setPct('porcentajeCitas', 0);
  setVal('citasCompletadas', completadas.length);
  setPct('porcentajeCitasCompletadas', 0);
  setVal('serviciosRealizados', serviciosRealizados.length);
  setPct('porcentajeServicios', 0);
  setVal('mascotasAtendidas', mascotasAtendidas);
  setPct('porcentajeMascotas', 0);
}

function renderActividadesFromCitas(payload) {
  const { citasClinica = [], userId } = payload || {};
  const { serviciosClinica = [], clinicaId } = payload || {};
  const today = new Date();
  const isSameDay = (dt) => dt && dt.getFullYear() === today.getFullYear() && dt.getMonth() === today.getMonth() && dt.getDate() === today.getDate();
  const toNum = (v) => v == null ? null : Number(v);
  const gid = toNum(userId);
  const mine = (c) => {
    const vet = toNum(c.veterinario_id);
    const creator = toNum(c.creada_por);
    return (vet != null && vet === gid) || (creator != null && creator === gid);
  };
  const actividades = citasClinica
    .filter((c) => {
      const d = c.fecha ? new Date(c.fecha) : null;
      return mine(c) && isSameDay(d);
    })
    .sort((a,b) => new Date(b.fecha) - new Date(a.fecha))
    .slice(0, 10)
    .map((c) => ({
      descripcion: (c.servicio?.nombre) || 'Servicio',
      created_at: c.fecha ? new Date(c.fecha).toLocaleString('es-MX') : ''
    }));
  const container = document.getElementById('actividadReciente');
  if(!container) return;
  container.innerHTML = '';
  if (!actividades || actividades.length === 0) {
    const mensaje = document.createElement('div');
    mensaje.className = 'text-center text-body-secondary py-3';
    mensaje.textContent = 'No hay actividades para hoy';
    container.appendChild(mensaje);
    return;
  }
  actividades.forEach((act) => {
    const row = document.createElement('div');
    row.className = 'd-flex gap-3 align-items-start border-bottom pb-3';
    row.innerHTML = `
      <div class="icon-bubble bg-opacity-25 bg-primary-subtle text-primary"><i class="bi bi-scissors"></i></div>
      <div>
        <div>${act.descripcion || 'Actividad'}</div>
        <div class="small text-body-secondary">${act.created_at || ''}</div>
      </div>
    `;
    container.appendChild(row);
  });
}

function setTodayTexts() {
  const today = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  const dateStr = `${pad(today.getDate())}/${pad(today.getMonth() + 1)}/${today.getFullYear()}`;
  const dateEl = document.getElementById('todayDate');
  if (dateEl) dateEl.textContent = dateStr;
  const textEl = document.getElementById('todayText');
  if (textEl) textEl.textContent = 'Resumen de hoy para grooming';
}

function getClinicName(data) {
  const d = data || {};
  // Intentar obtener el nombre de clínica de distintas fuentes del payload
  return d.clinicaNombre || d.usuario?.clinica?.nombre || d.clinica?.nombre || '';
}

function renderSection(section, data) {
  const mainContent = document.getElementById('mainContent');
  if (!mainContent) return;
  mainContent.innerHTML = '';
  if (section === 'home') {
    const userName = mainContent.dataset.usuarioNombre || 'Usuario';
    const clinicaNombre = getClinicName(data);
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">¡Hola, ${userName}! ✂️</h1>
        <p class="text-body-secondary small" id="todayText">Resumen de hoy para grooming</p>
        ${clinicaNombre ? `<div class="small text-body-secondary">Clínica: <span class="fw-semibold">${clinicaNombre}</span></div>` : ''}
      </div>
      <div class="row g-3 g-lg-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Citas de Hoy</div>
                <div class="h4 mb-1" id="citasHoy">0</div>
                <div class="small text-success" id="porcentajeCitas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-primary-subtle text-primary"><i class="bi bi-calendar-check"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Citas Completadas</div>
                <div class="h4 mb-1" id="citasCompletadas">0</div>
                <div class="small text-success" id="porcentajeCitasCompletadas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-success text-success"><i class="bi bi-clipboard-check"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Servicios Realizados</div>
                <div class="h4 mb-1" id="serviciosRealizados">0</div>
                <div class="small text-success" id="porcentajeServicios">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-info text-info"><i class="bi bi-scissors"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Mascotas Atendidas</div>
                <div class="h4 mb-1" id="mascotasAtendidas">0</div>
                <div class="small text-success" id="porcentajeMascotas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-warning text-warning"><i class="bi bi-heart"></i></div>
            </div>
          </div>
        </div>
      </div>
      <div class="row g-3 g-lg-4 mb-4">
        <div class="col-12">
          <div class="card card-soft p-4 h-100">
            <h3 class="h6 mb-3">Citas Esta Semana</h3>
            <div class="chart-container">
              <canvas id="appointmentsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    `;
  } else if (section === 'agenda') {
    const clinicaNombre = getClinicName(data);
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Mi Agenda</h1>
        <p class="text-body-secondary small">Citas asignadas para hoy</p>
        ${clinicaNombre ? `<div class="small text-body-secondary">Clínica: <span class="fw-semibold">${clinicaNombre}</span></div>` : ''}
      </div>
      <div class="card card-soft p-4">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>HORA</th>
                <th>MASCOTA</th>
                <th>PROPIETARIO</th>
                <th>CLÍNICA</th>
                <th>SERVICIO</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
              </tr>
            </thead>
            <tbody id="agendaBody">
              <tr>
                <td colspan="7" class="text-center text-body-secondary py-4">No hay citas programadas</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `;
    // Si ya tenemos data, renderizar agenda
    if (data && data.citasClinica) {
      renderAgendaFromCitas(data);
    }
  } else if (section === 'historial') {
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Historial</h1>
        <p class="text-body-secondary small">Historial de servicios de grooming</p>
      </div>
      <div class="card card-soft p-4">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>Fecha</th>
                <th>Mascota</th>
                <th>Servicio</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody id="historialBody">
              <tr>
                <td colspan="4" class="text-center text-body-secondary py-4">No hay historial disponible</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `;
  } else if (section === 'configuracion') {
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Configuración</h1>
        <p class="text-body-secondary small">Preferencias del panel de grooming</p>
      </div>
      <div class="card card-soft p-4">
        <div class="text-body-secondary">Próximamente</div>
      </div>
    `;
  }
}

function renderAgendaFromCitas(payload) {
  const { citasClinica = [], userId, serviciosClinica = [], clinicaId } = payload || {};
  const clinicaNombreGlobal = getClinicName(payload);
  const toNum = (v) => v == null ? null : Number(v);
  const agendaBody = document.getElementById('agendaBody');
  if (!agendaBody) return;
  const gid = toNum(userId);
  const mine = (c) => {
    const vet = toNum(c.veterinario_id);
    const creator = toNum(c.creada_por);
    return (vet != null && vet === gid) || (creator != null && creator === gid);
  };
  // Listar todas las citas asignadas al usuario actual (para mí), sin filtrar por tipo de servicio
  const citas = (citasClinica || [])
    .filter((c) => mine(c))
    .sort((a,b) => new Date(a.fecha) - new Date(b.fecha));
  if (citas.length === 0) {
    agendaBody.innerHTML = `<tr><td colspan="7" class="text-center text-body-secondary py-4">No hay citas programadas</td></tr>`;
    return;
  }
  agendaBody.innerHTML = '';
  citas.forEach((c) => {
    const hora = c.fecha ? new Date(c.fecha).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) : '';
    const mascota = c.mascota?.nombre || 'Mascota';
    const propietario = c.mascota?.propietario?.nombre || c.creador?.nombre || c.veterinario?.nombre || '';
    const clinicaNombre = c.clinica?.nombre || clinicaNombreGlobal || 'Clínica';
    const servicio = c.servicio?.nombre || 'Servicio';
    const estado = c.status || '';
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${hora}</td>
      <td>${mascota}</td>
      <td>${propietario}</td>
      <td>${clinicaNombre}</td>
      <td>${servicio}</td>
      <td>${estado}</td>
      <td>
        <button class="btn btn-sm btn-outline-primary" data-action="ver" data-id="${c.id}">Ver</button>
      </td>
    `;
    agendaBody.appendChild(tr);
  });
}

function markActive(section) {
  document.querySelectorAll('.nav-btn').forEach((b) => b.classList.remove('active'));
  const btn = document.querySelector(`.nav-btn [data-section="${section}"]`)?.closest('.nav-btn') ||
              document.querySelector(`.nav-btn[data-section="${section}"]`);
  if (btn) btn.classList.add('active');
}

function initNavHandlers() {
  document.querySelectorAll('.nav-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const section = btn.dataset.section || btn.querySelector('[data-section]')?.dataset.section;
      if (!section) return;
      document.querySelectorAll('.nav-btn').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      if (section === 'home') {
        fetch('/groomer-dashboard/data')
          .then((res) => res.json())
          .then((data) => {
            renderSection('home', data);
            const { labels, data: series } = buildChartSeriesFromCitas(data.citasClinica, data.userId, data.serviciosClinica, data.clinicaId);
            updateDashboardMetricsFromCitas(data);
            renderChart(labels, series);
            renderActividadesFromCitas(data);
            setTodayTexts();
          });
        return;
      }
      // Para otras secciones, también obtener datos y renderizar lista si aplica
      fetch('/groomer-dashboard/data')
        .then((res) => res.json())
        .then((data) => {
          renderSection(section, data);
          if (section === 'agenda') {
            renderAgendaFromCitas(data);
          }
        });
      history.pushState({ section }, '', `/groomer-dashboard/${section}`);
    });
  });
}

function handlePopState() {
  window.addEventListener('popstate', (event) => {
    const match = location.pathname.match(/^\/groomer-dashboard(?:\/([^\/?#]+))?/);
    const section = event.state?.section || (match && match[1]) || 'home';
    markActive(section);
    if (section === 'home') {
      fetch('/groomer-dashboard/data')
        .then((res) => res.json())
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeriesFromCitas(data.citasClinica, data.userId, data.serviciosClinica, data.clinicaId);
          updateDashboardMetricsFromCitas(data);
          renderChart(labels, series);
          renderActividadesFromCitas(data);
          setTodayTexts();
        });
    } else {
      fetch('/groomer-dashboard/data')
        .then((res) => res.json())
        .then((data) => {
          renderSection(section, data);
          if (section === 'agenda') {
            renderAgendaFromCitas(data);
          }
        });
    }
  });
}

(function() {
  document.addEventListener('DOMContentLoaded', () => {
    const match = location.pathname.match(/^\/groomer-dashboard(?:\/([^\/?#]+))?/);
    const initialSection = (match && match[1]) ? match[1] : 'home';
    markActive(initialSection);
    // Delegación para abrir modal al hacer click en "Ver"
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-action="ver"][data-id]');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (!id) return;
      openCitaModal(id);
    });

    // Acción de completar cita desde el modal
    const completarBtn = document.getElementById('citaModalCompletar');
    if (completarBtn) {
      completarBtn.addEventListener('click', () => {
        if (!window.__currentCitaId) return;
        completeCita(window.__currentCitaId);
      });
    }

    if (initialSection === 'home') {
      fetch('/groomer-dashboard/data')
        .then((r) => r.json())
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeriesFromCitas(data.citasClinica, data.userId, data.serviciosClinica, data.clinicaId);
          updateDashboardMetricsFromCitas(data);
          renderChart(labels, series);
          renderActividadesFromCitas(data);
          setTodayTexts();
        })
        .catch((err) => console.error('Error al obtener los datos del dashboard groomer:', err));
    } else {
      fetch('/groomer-dashboard/data')
        .then((r) => r.json())
        .then((data) => {
          renderSection(initialSection, data);
          if (initialSection === 'agenda') {
            renderAgendaFromCitas(data);
          }
        });
      history.replaceState({ section: initialSection }, '', location.pathname);
    }

    initNavHandlers();
    handlePopState();
  });
})();

// Helpers y lógica de modal
function getCsrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

function showAlertInModal(msg) {
  const box = document.getElementById('citaModalAlert');
  if (!box) return;
  if (!msg) { box.classList.add('d-none'); box.textContent = ''; return; }
  box.textContent = msg;
  box.classList.remove('d-none');
}

function populateCitaModal(data) {
  const d = data || {};
  const cita = d.cita || d; // por si el endpoint devuelve {cita: {...}}
  document.getElementById('citaModalServicio')?.replaceChildren(document.createTextNode(cita?.servicio?.nombre || 'Servicio'));
  document.getElementById('citaModalMascota')?.replaceChildren(document.createTextNode(cita?.mascota?.nombre || 'Mascota'));
  const propietario = cita?.mascota?.propietario?.nombre || cita?.mascota?.user?.name || cita?.creador?.nombre || cita?.veterinario?.nombre || '';
  document.getElementById('citaModalPropietario')?.replaceChildren(document.createTextNode(propietario));
  const fechaStr = cita?.fecha ? new Date(cita.fecha).toLocaleString('es-MX') : '';
  document.getElementById('citaModalFecha')?.replaceChildren(document.createTextNode(fechaStr));
  const estadoEl = document.getElementById('citaModalEstado');
  if (estadoEl) {
    const estado = cita?.status || '';
    estadoEl.textContent = estado;
    estadoEl.className = 'badge ' + (estado === 'completada' ? 'bg-success' : 'bg-secondary');
  }
  const notasEl = document.getElementById('citaModalNotas');
  if (notasEl) notasEl.value = cita?.notas || '';
}

function openCitaModal(id) {
  showAlertInModal('');
  window.__currentCitaId = Number(id);
  // Mostrar el modal inmediatamente para feedback visual
  const modalEl = document.getElementById('citaModal');
  if (modalEl && typeof bootstrap !== 'undefined') {
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }
  // Limpiar contenido mientras carga
  populateCitaModal({ cita: { servicio: { nombre: 'Cargando...' }, mascota: { nombre: '' }, status: '', fecha: null, notas: '' } });
  fetch(`/groomer-dashboard/citas/${id}`)
    .then((r) => {
      if (!r.ok) throw new Error('No se pudo cargar la cita');
      return r.json();
    })
    .then((data) => {
      populateCitaModal(data);
    })
    .catch((err) => {
      console.error(err);
      showAlertInModal('Error al cargar la cita. Intenta de nuevo.');
    });
}

function completeCita(id) {
  const notas = document.getElementById('citaModalNotas')?.value || '';
  const token = getCsrfToken();
  showAlertInModal('');
  fetch(`/groomer-dashboard/citas/${id}/complete`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': token,
    },
    body: JSON.stringify({ notas }),
  })
    .then((r) => {
      if (!r.ok) return r.json().then((j) => { throw new Error(j?.message || 'No se pudo completar la cita'); });
      return r.json();
    })
    .then((resp) => {
      // Cerrar modal
      const modalEl = document.getElementById('citaModal');
      if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
      }
      // Refrescar la agenda para reflejar el estado actualizado
      fetch('/groomer-dashboard/data')
        .then((r) => r.json())
        .then((data) => {
          // Si estamos en la sección agenda, re-renderizar; si no, no cambiamos sección
          const match = location.pathname.match(/^\/groomer-dashboard(?:\/([^\/?#]+))?/);
          const section = (match && match[1]) || 'home';
          if (section === 'agenda') {
            renderSection('agenda', data);
            renderAgendaFromCitas(data);
          }
        });
    })
    .catch((err) => {
      console.error(err);
      showAlertInModal(err?.message || 'Error al completar la cita');
    });
}
