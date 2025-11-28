// public/js/views/dashboard-groomer.js

let chartInstance = null;

function isGroomingServiceName(name) {
  if (!name) return false;
  const n = String(name).toLowerCase();
  return [
    'corte', 'baño', 'limpieza dental', 'spa', 'uñas', 'desparasitación',
    'peluquer', 'groom', 'aseo'
  ].some((kw) => n.includes(kw));
}

function buildChartSeriesFromCitas(citas, groomerId, serviciosClinica) {
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
  const gid = toNum(groomerId);
  const groomingServiceIds = new Set(
    (serviciosClinica || [])
      .filter((s) => isGroomingServiceName(s.nombre))
      .map((s) => toNum(s.id))
      .filter((id) => id != null)
  );
  const isMine = (c) => {
    const vet = toNum(c.veterinario_id);
    const creator = toNum(c.creada_por);
    return (vet != null && vet === gid) || (creator != null && creator === gid);
  };
  const isGroomingByService = (c) => {
    const sid = toNum(c.servicio_id);
    if (sid != null && groomingServiceIds.size > 0) return groomingServiceIds.has(sid);
    return c.servicio?.nombre ? isGroomingServiceName(c.servicio.nombre) : true;
  };
  (citas || [])
    .filter((c) => isMine(c) && isGroomingByService(c))
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
  const { serviciosClinica = [] } = payload || {};
  const today = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  const isSameDay = (dt) => dt && dt.getFullYear() === today.getFullYear() && dt.getMonth() === today.getMonth() && dt.getDate() === today.getDate();
  const toNum = (v) => v == null ? null : Number(v);
  const gid = toNum(userId);
  const groomingServiceIds = new Set(
    (serviciosClinica || [])
      .filter((s) => isGroomingServiceName(s.nombre))
      .map((s) => toNum(s.id))
      .filter((id) => id != null)
  );
  const mine = (c) => {
    const vet = toNum(c.veterinario_id);
    const creator = toNum(c.creada_por);
    return (vet != null && vet === gid) || (creator != null && creator === gid);
  };
  const isGroomingByService = (c) => {
    const sid = toNum(c.servicio_id);
    if (sid != null && groomingServiceIds.size > 0) return groomingServiceIds.has(sid);
    return c.servicio?.nombre ? isGroomingServiceName(c.servicio.nombre) : true;
  };
  const citasHoy = citasClinica.filter((c) => {
    const d = c.fecha ? new Date(c.fecha) : null;
    return mine(c) && isGroomingByService(c) && isSameDay(d);
  });
  const completadas = citasHoy.filter((c) => c.status === 'completada');
  const serviciosRealizados = citasHoy.filter((c) => isGroomingByService(c));
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
  const { serviciosClinica = [] } = payload || {};
  const today = new Date();
  const isSameDay = (dt) => dt && dt.getFullYear() === today.getFullYear() && dt.getMonth() === today.getMonth() && dt.getDate() === today.getDate();
  const toNum = (v) => v == null ? null : Number(v);
  const gid = toNum(userId);
  const groomingServiceIds = new Set(
    (serviciosClinica || [])
      .filter((s) => isGroomingServiceName(s.nombre))
      .map((s) => toNum(s.id))
      .filter((id) => id != null)
  );
  const mine = (c) => {
    const vet = toNum(c.veterinario_id);
    const creator = toNum(c.creada_por);
    return (vet != null && vet === gid) || (creator != null && creator === gid);
  };
  const actividades = citasClinica
    .filter((c) => {
      const d = c.fecha ? new Date(c.fecha) : null;
      const sid = toNum(c.servicio_id);
      const groomingOk = sid != null && groomingServiceIds.size > 0 ? groomingServiceIds.has(sid) : (c.servicio?.nombre ? isGroomingServiceName(c.servicio.nombre) : true);
      return mine(c) && groomingOk && isSameDay(d);
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
    mensaje.textContent = 'No hay actividades de grooming en este momento';
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

function renderSection(section, data) {
  const mainContent = document.getElementById('mainContent');
  if (!mainContent) return;
  mainContent.innerHTML = '';
  if (section === 'home') {
    const userName = mainContent.dataset.usuarioNombre || 'Usuario';
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">¡Hola, ${userName}! ✂️</h1>
        <p class="text-body-secondary small" id="todayText">Resumen de hoy para grooming</p>
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
        <div class="col-12 col-lg-8">
          <div class="card card-soft p-4 h-100">
            <h3 class="h6 mb-3">Citas Esta Semana</h3>
            <div class="chart-container">
              <canvas id="appointmentsChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="card card-soft p-4 h-100">
            <h3 class="h6 mb-3">Mi Agenda de Hoy</h3>
            <div class="vstack gap-3" id="actividadReciente">
              No hay actividades de grooming en este momento
            </div>
          </div>
        </div>
      </div>
    `;
  } else if (section === 'agenda') {
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Mi Agenda</h1>
        <p class="text-body-secondary small">Citas asignadas para hoy</p>
      </div>
      <div class="card card-soft p-4">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>HORA</th>
                <th>MASCOTA</th>
                <th>PROPIETARIO</th>
                <th>SERVICIO</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
              </tr>
            </thead>
            <tbody id="agendaBody">
              <tr>
                <td colspan="6" class="text-center text-body-secondary py-4">No hay citas programadas</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `;
  } else if (section === 'actividad') {
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Actividad de Hoy</h1>
        <p class="text-body-secondary small">Servicios y tareas realizadas</p>
      </div>
      <div class="card card-soft p-4">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>HORA</th>
                <th>MASCOTA</th>
                <th>SERVICIO</th>
                <th>ESTADO</th>
              </tr>
            </thead>
            <tbody id="actividadHoyBody">
              <tr>
                <td colspan="4" class="text-center text-body-secondary py-5">Sin actividad registrada</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `;
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
            const { labels, data: series } = buildChartSeriesFromCitas(data.citasClinica, data.userId, data.serviciosClinica);
            updateDashboardMetricsFromCitas(data);
            renderChart(labels, series);
            renderActividadesFromCitas(data);
            setTodayTexts();
          });
        return;
      }
      renderSection(section);
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
          const { labels, data: series } = buildChartSeriesFromCitas(data.citasClinica, data.userId, data.serviciosClinica);
          updateDashboardMetricsFromCitas(data);
          renderChart(labels, series);
          renderActividadesFromCitas(data);
          setTodayTexts();
        });
    } else {
      renderSection(section);
    }
  });
}

(function() {
  document.addEventListener('DOMContentLoaded', () => {
    const match = location.pathname.match(/^\/groomer-dashboard(?:\/([^\/?#]+))?/);
    const initialSection = (match && match[1]) ? match[1] : 'home';
    markActive(initialSection);

    if (initialSection === 'home') {
      fetch('/groomer-dashboard/data')
        .then((r) => r.json())
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeriesFromCitas(data.citasClinica, data.userId, data.serviciosClinica);
          updateDashboardMetricsFromCitas(data);
          renderChart(labels, series);
          renderActividadesFromCitas(data);
          setTodayTexts();
        })
        .catch((err) => console.error('Error al obtener los datos del dashboard groomer:', err));
    } else {
      renderSection(initialSection);
      history.replaceState({ section: initialSection }, '', location.pathname);
    }

    initNavHandlers();
    handlePopState();
  });
})();
