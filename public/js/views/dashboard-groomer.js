// public/js/views/dashboard-groomer.js

let chartInstance = null;

function buildChartSeries(citasPorDia) {
  const diasOrdenados = [
    { en: 'Monday', es: 'Lun', esFull: 'Lunes' },
    { en: 'Tuesday', es: 'Mar', esFull: 'Martes' },
    { en: 'Wednesday', es: 'Mié', esFull: 'Miércoles' },
    { en: 'Thursday', es: 'Jue', esFull: 'Jueves' },
    { en: 'Friday', es: 'Vie', esFull: 'Viernes' },
    { en: 'Saturday', es: 'Sáb', esFull: 'Sábado' },
    { en: 'Sunday', es: 'Dom', esFull: 'Domingo' },
  ];

  // Build a lookup from possible incoming day strings (en, es short, es full) to the English key we use for ordering
  const enByLabel = {};
  diasOrdenados.forEach((d) => {
    enByLabel[d.en.toLowerCase()] = d.en;
    enByLabel[d.es.toLowerCase()] = d.en;
    if (d.esFull) enByLabel[d.esFull.toLowerCase()] = d.en;
  });

  // Aggregate totals mapping to English day names so the ordering below works regardless of backend locale
  const map = {};
  (citasPorDia || []).forEach((item) => {
    const raw = (item && item.dia) ? String(item.dia).trim() : '';
    const key = raw.toLowerCase();
    const en = enByLabel[key] || raw; // fallback to raw if unknown
    map[en] = (map[en] || 0) + (Number(item.total) || 0);
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

function updateDashboardMetrics(d) {
  const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  const setPct = (id, pct) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = (pct >= 0 ? '+' : '') + pct + '%';
    el.className = 'small ' + (pct >= 0 ? 'text-success' : 'text-danger');
  };
  setVal('citasHoy', d.citasHoy);
  setPct('porcentajeCitas', d.comparacionporcentaje?.citasHoy ?? 0);
  setVal('citasCompletadas', d.citasCompletadas);
  setPct('porcentajeCitasCompletadas', d.comparacionporcentaje?.citasCompletadas ?? 0);
  setVal('serviciosRealizados', d.serviciosRealizados || 0);
  setPct('porcentajeServicios', d.comparacionporcentaje?.serviciosRealizados ?? 0);
  setVal('mascotasAtendidas', d.mascotasAtendidas || 0);
  setPct('porcentajeMascotas', d.comparacionporcentaje?.mascotasAtendidas ?? 0);
}

function renderActividades(actividades) {
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

function renderActividadHoy(citas) {
  const tbody = document.getElementById('actividadHoyBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const rows = (citas || []).filter(Boolean);
  if (rows.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="4" class="text-center text-body-secondary py-5">Sin actividad registrada</td>
      </tr>
    `;
    return;
  }

  rows.forEach((c) => {
    const hora = c.hora || c.start_time || c.time || (c.fecha ? new Date(c.fecha).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '');
    const mascota = (c.mascota && (c.mascota.nombre || c.mascota.nombre_completo)) || c.mascota || (c.mascota_nombre) || '—';
    const servicio = (c.servicio && (c.servicio.nombre || c.servicio.title)) || c.servicio || c.servicio_nombre || '—';
    const estado = c.estado || c.status || (c.estado_cita) || '—';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="width:110px">${hora}</td>
      <td>${mascota}</td>
      <td>${servicio}</td>
      <td>${estado}</td>
    `;
    tbody.appendChild(tr);
  });
}

function extractActividadHoyFromData(data) {
  // Try multiple possible keys that backend might return
  if (!data) return [];
  if (Array.isArray(data.actividadHoy)) return data.actividadHoy;
  if (Array.isArray(data.actividadesHoy)) return data.actividadesHoy;
  if (Array.isArray(data.actividades)) return data.actividades;
  if (Array.isArray(data.citasHoy)) return data.citasHoy;
  if (Array.isArray(data.citas)) return data.citas;
  return [];
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
            const { labels, data: series } = buildChartSeries(data.citasPorDia);
            updateDashboardMetrics(data);
            renderChart(labels, series);
            renderActividades(data.actividades);
            setTodayTexts();
          });
        return;
      }
      if (section === 'actividad') {
        // Fetch data and populate the actividad table
        fetch('/groomer-dashboard/data')
          .then((res) => res.json())
          .then((data) => {
            renderSection('actividad');
            const actividadesHoy = extractActividadHoyFromData(data);
            renderActividadHoy(actividadesHoy);
          })
          .catch(() => {
            renderSection('actividad');
          });
        history.pushState({ section }, '', `/groomer-dashboard/${section}`);
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
          const { labels, data: series } = buildChartSeries(data.citasPorDia);
          updateDashboardMetrics(data);
          renderChart(labels, series);
          renderActividades(data.actividades);
          setTodayTexts();
        });
    } else if (section === 'actividad') {
      fetch('/groomer-dashboard/data')
        .then((res) => res.json())
        .then((data) => {
          renderSection('actividad');
          const actividadesHoy = extractActividadHoyFromData(data);
          renderActividadHoy(actividadesHoy);
        })
        .catch(() => renderSection('actividad'));
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
          const { labels, data: series } = buildChartSeries(data.citasPorDia);
          updateDashboardMetrics(data);
          renderChart(labels, series);
          renderActividades(data.actividades);
          setTodayTexts();
        })
        .catch((err) => console.error('Error al obtener los datos del dashboard groomer:', err));
    } else {
      if (initialSection === 'actividad') {
        fetch('/groomer-dashboard/data')
          .then((r) => r.json())
          .then((data) => {
            renderSection('actividad');
            const actividadesHoy = extractActividadHoyFromData(data);
            renderActividadHoy(actividadesHoy);
          })
          .catch(() => renderSection('actividad'));
      } else {
        renderSection(initialSection);
      }
      history.replaceState({ section: initialSection }, '', location.pathname);
    }

    initNavHandlers();
    handlePopState();
  });
})();
