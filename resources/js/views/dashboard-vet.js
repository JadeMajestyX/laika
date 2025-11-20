// public/js/views/dashboard-vet.js

let chartInstance = null;

function buildChartSeries(citasPorDia) {
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
  (citasPorDia || []).forEach((item) => {
    map[item.dia] = item.total;
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
  const barColor = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#3A7CA5';
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
  const setVal = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };
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
  setVal('consultasRealizadas', d.consultasRealizadas || 0);
  setPct('porcentajeConsultas', d.comparacionporcentaje?.consultasRealizadas ?? 0);
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
    mensaje.textContent = 'No hay citas disponibles en este momento';
    container.appendChild(mensaje);
    return;
  }

  actividades.forEach((actividad) => {
    const row = document.createElement('div');
    row.className = 'd-flex gap-3 align-items-start border-bottom pb-3';

    if(actividad.modelo === 'User'){
      row.innerHTML = `
        <div class="icon-bubble bg-opacity-25 bg-warning text-warning"><i class="bi bi-person-plus"></i></div>
        <div>
          <div>Nuevo cliente registrado: ${actividad.user.nombre}</div>
          <div class="small text-body-secondary">${actividad.created_at}</div>
        </div>
      `;
      container.appendChild(row);
    } else if(actividad.modelo === 'Mascota'){
      row.innerHTML = `
        <div class="icon-bubble bg-opacity-25 bg-info text-info"><i class="bi bi-heart"></i></div>
        <div>
          <div>Nueva mascota registrada: ${actividad.user.nombre}</div>
          <div class="small text-body-secondary">${actividad.created_at}</div>
        </div>
      `;
      container.appendChild(row);
    }
  });
}

function setTodayTexts() {
  const today = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  const dateStr = `${pad(today.getDate())}/${pad(today.getMonth() + 1)}/${today.getFullYear()}`;
  const dateEl = document.getElementById('todayDate');
  if (dateEl) dateEl.textContent = dateStr;
  const textEl = document.getElementById('todayText');
  if (textEl) textEl.textContent = 'Aquí está el resumen de hoy';
}

function renderSection(section, data) {
  const mainContent = document.getElementById('mainContent');
  if (!mainContent) return;
  mainContent.innerHTML = '';
  
  if (section === 'home') {
    const userName = mainContent.dataset.usuarioNombre || 'Usuario';
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">¡Bienvenido, ${userName}! 👋</h1>
        <p class="text-body-secondary small" id="todayText">Aquí está el resumen de hoy</p>
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
                <div class="text-body-secondary small mb-1">Consultas Realizadas</div>
                <div class="h4 mb-1" id="consultasRealizadas">0</div>
                <div class="small text-success" id="porcentajeConsultas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-info text-info"><i class="bi bi-heart-pulse"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Mascotas Atendidas Hoy</div>
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
            <h3 class="h6 mb-3">Citas disponibles</h3>
            <div class="vstack gap-3" id="actividadReciente">
              No hay citas disponibles en este momento
            </div>
          </div>
        </div>
      </div>
    `;
  } else if (section === 'actividad') {
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Actividad de Hoy</h1>
        <p class="text-body-secondary small">Registro de actividades y procedimientos del día actual</p>
      </div>
      
      <div class="card card-soft p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="h6 mb-0">📋 Registro de Actividades</h3>
          <span class="badge bg-primary">Hoy</span>
        </div>
        
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>HORA</th>
                <th>PACIENTE</th>
                <th>PROPIETARIO</th>
                <th>ESPECIE</th>
                <th>RAZA</th>
                <th>TIPO DE ACTIVIDAD</th>
                <th>PROCEDIMIENTO</th>
                <th>ESTADO</th>
              </tr>
            </thead>
            <tbody id="actividadHoyBody">
              <tr>
                <td colspan="8" class="text-center text-body-secondary py-5">
                  <div class="py-3">
                    <i class="bi bi-calendar-check fs-1 text-body-secondary opacity-50 d-block mb-3"></i>
                    <h6 class="text-body-secondary">No hay actividades registradas para hoy</h6>
                    <small class="text-body-secondary">Las actividades aparecerán aquí conforme se realicen procedimientos</small>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="mt-3 pt-3 border-top">
          <div class="row text-center">
            <div class="col-4">
              <div class="text-body-secondary small">Total Actividades</div>
              <div class="h5 mb-0">0</div>
            </div>
            <div class="col-4">
              <div class="text-body-secondary small">En Progreso</div>
              <div class="h5 mb-0 text-warning">0</div>
            </div>
            <div class="col-4">
              <div class="text-body-secondary small">Completadas</div>
              <div class="h5 mb-0 text-success">0</div>
            </div>
          </div>
        </div>
      </div>
    `;
} else if (section === 'historial') {
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Historial</h1>
        <p class="text-body-secondary small">Historial de consultas y procedimientos</p>
      </div>
      <div class="card card-soft p-4">
        <h3 class="h6 mb-3">Historial de Consultas</h3>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Propietario</th>
                <th>Procedimiento</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody id="historialBody">
              <tr>
                <td colspan="5" class="text-center text-body-secondary py-4">No hay historial disponible</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `;
  } else if (section === 'reportes') {
    const userName = mainContent.dataset.usuarioNombre || 'Usuario';
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Reportes</h1>
        <p class="text-body-secondary small">Reportes y estadísticas veterinarias</p>
      </div>
      
      <!-- Filtros -->
      <div class="card card-soft p-4 mb-4">
        <div class="row g-3 align-items-end">
          <div class="col-12">
            <h5 class="mb-3">Rango de fechas:</h5>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Periodo</label>
            <select class="form-select" id="filtro-periodo">
              <option value="este-mes" selected>Este mes</option>
              <option value="mes-anterior">Mes anterior</option>
              <option value="trimestre-actual">Trimestre actual</option>
              <option value="personalizado">Personalizado</option>
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Desde</label>
            <input type="date" class="form-control" id="filtro-desde" value="2025-09-01">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Hasta</label>
            <input type="date" class="form-control" id="filtro-hasta" value="2025-09-15">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Trabajador</label>
            <select class="form-select" id="filtro-trabajador" disabled>
              <option selected> ${userName} </option>
            </select> 
          </div>
          <div class="col-12 col-md-6 d-flex gap-2 align-items-end">
            <button class="btn btn-primary flex-fill" id="btn-aplicar-filtro">Aplicar filtro</button>
            <button class="btn btn-outline-secondary" id="btn-exportar">Exportar</button>
          </div>
        </div>
      </div>

      <!-- Métricas principales -->
      <div class="row g-3 g-lg-4 mb-4" id="metricas-principales">
        <div class="col-12 col-md-4">
          <div class="card card-soft p-4 text-center">
            <div class="h2 text-primary mb-2" id="metric-citas">--</div>
            <div class="text-body-secondary">Citas realizadas</div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card card-soft p-4 text-center">
            <div class="h2 text-primary mb-2" id="metric-consultas">--</div>
            <div class="text-body-secondary">Consultas realizadas</div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card card-soft p-4 text-center">
            <div class="h2 text-primary mb-2" id="metric-mascotas">--</div>
            <div class="text-body-secondary">Mascotas atendidas</div>
          </div>
        </div>
      </div>

      <!-- Gráficas -->
      <div class="row g-3 g-lg-4 mb-4">
        <div class="col-12 col-md-6">
          <div class="card card-soft p-4">
            <h5 class="mb-3">Mascotas atendidas</h5>
            <div class="chart-container" style="height: 300px; position: relative;">
              <canvas id="graficaMascotasAtendidas"></canvas>
              <div id="mensaje-sin-datos-mascotas" class="d-none text-center p-5">
                <div class="text-body-secondary mb-2">
                  <i class="bi bi-bar-chart" style="font-size: 3rem;"></i>
                </div>
                <p class="text-muted">No hay datos de mascotas atendidas para el periodo seleccionado</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="card card-soft p-4">
            <h5 class="mb-3">Mascotas por especie</h5>
            <div class="chart-container" style="height: 300px; position: relative;">
              <canvas id="graficaMascotasEspecie"></canvas>
              <div id="mensaje-sin-datos-especies" class="d-none text-center p-5">
                <div class="text-body-secondary mb-2">
                  <i class="bi bi-pie-chart" style="font-size: 3rem;"></i>
                </div>
                <p class="text-muted">No hay datos de especies para el periodo seleccionado</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de resumen -->
      <div class="card card-soft p-4">
        <h5 class="mb-3">Resumen de citas</h5>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Estado</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
                <th>Tendencia</th>
              </tr>
            </thead>
            <tbody id="tabla-resumen-citas">
              <tr id="fila-sin-datos">
                <td colspan="4" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>
                  Selecciona un rango de fechas para ver el resumen de citas
                </td>
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
        <p class="text-body-secondary small">Configuración del panel</p>
      </div>
      <div class="card card-soft p-4">
        <h3 class="h6 mb-3">Preferencias</h3>
        <div class="vstack gap-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="notificacionesSwitch" checked>
            <label class="form-check-label" for="notificacionesSwitch">Notificaciones de urgencias</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="recordatoriosSwitch" checked>
            <label class="form-check-label" for="recordatoriosSwitch">Recordatorios de citas</label>
          </div>
        </div>
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
        fetch('/vet-dashboard/data')
          .then((res) => res.json())
          .then((data) => {
            renderSection('home', data);
            const { labels, data: series } = buildChartSeries(data.citasPorDia);
            updateDashboardMetrics(data);
            renderChart(labels, series);
            renderActividades(data.actividades);
            setTodayTexts();
            history.pushState({ section: 'home' }, '', '/vet-dashboard/home');
          });
        return;
      }

      renderSection(section);
      history.pushState({ section }, '', `/vet-dashboard/${section}`);
    });
  });
}

function handlePopState() {
  window.addEventListener('popstate', (event) => {
    const section = event.state?.section || (location.pathname.replace('/vet-dashboard/')[2] || 'home');
    markActive(section);
    if (section === 'home') {
      fetch('/vet-dashboard/data')
        .then((res) => res.json())
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeries(data.citasPorDia);
          updateDashboardMetrics(data);
          renderChart(labels, series);
          renderActividades(data.actividades);
          setTodayTexts();
        });
    } else {
      renderSection(section);
    }
  });
}

// Inicialización
(function() {
  document.addEventListener('DOMContentLoaded', () => {
    const match = location.pathname.match(/^\/vet-dashboard(?:\/([^\/?#]+))?/);
    const initialSection = (match && match[1]) ? match[1] : 'home';
    markActive(initialSection);

    if (initialSection === 'home') {
      fetch('/vet-dashboard/data')
        .then((r) => r.json())
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeries(data.citasPorDia);
          updateDashboardMetrics(data);
          renderChart(labels, series);
          renderActividades(data.actividades);
          setTodayTexts();
          history.replaceState({ section: 'home' }, '', location.pathname);
        })
        .catch((err) => console.error('Error al obtener los datos del dashboard:', err));
    } else {
      renderSection(initialSection);
      history.replaceState({ section: initialSection }, '', location.pathname);
    }

    initNavHandlers();
    handlePopState();
  });
})();