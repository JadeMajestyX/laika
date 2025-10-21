// public/js/views/dashboard.js
// JS específico para la vista dashboard (SPA secciones Home y Mascotas) sin Vite ni ESM

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
  setVal('mascotasRegistradas', d.mascotasRegistradas);
  setPct('porcentajeMascotasRegistradas', d.comparacionporcentaje?.mascotasRegistradas ?? 0);
  setVal('clientesNuevos', d.clientesNuevos);
  setPct('porcentajeClientesNuevos', d.comparacionporcentaje?.clientesNuevos ?? 0);
}

function renderAppointments(appointments) {
  const tbody = document.getElementById('appointmentsBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  (appointments || []).forEach((apt) => {
    const badge = ((status) => {
      switch (status) {
        case 'completada':
          return '<span class="badge bg-success-subtle text-success border-0">Completada</span>';
        case 'confirmada':
          return '<span class="badge bg-warning-subtle text-warning border-0">Confirmada</span>';
        case 'pendiente':
          return '<span class="badge bg-primary-subtle text-primary border-0">Pendiente</span>';
        default:
          return '<span class="badge bg-secondary text-dark border-0">-</span>';
      }
    })(apt.status);
    const petIcon = 'bi-heart';
    const row = document.createElement('tr');
    row.innerHTML = `
          <td class="text-body-secondary">${apt.time}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar" style="width:32px;height:32px;border-radius:0.5rem;" aria-hidden="true">
                <i class="bi ${petIcon} text-primary"></i>
              </div>
              <span>${apt.pet}</span>
            </div>
          </td>
          <td>${apt.owner}</td>
          <td class="text-body-secondary">${apt.breed}</td>
          <td class="text-body-secondary">${apt.reason}</td>
          <td class="text-body-secondary">${apt.clinic}</td>
          <td>${badge}</td>
        `;
    tbody.appendChild(row);
  });
}

function renderActividades(actividades) {
  const container = document.getElementById('actividadReciente');
  if(!container) return;

  container.innerHTML = '';
  (actividades || []).forEach((actividad) => {
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

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.getAttribute('content') || '';
}

function formatAge(dateStr) {
  if (!dateStr) return '-';
  const dob = new Date(dateStr);
  if (isNaN(dob)) return '-';
  const now = new Date();
  let years = now.getFullYear() - dob.getFullYear();
  const mDiff = now.getMonth() - dob.getMonth();
  if (mDiff < 0 || (mDiff === 0 && now.getDate() < dob.getDate())) years--;
  if (years > 0) return years + ' años';
  let months = (now.getFullYear() - dob.getFullYear()) * 12 + (now.getMonth() - dob.getMonth());
  if (now.getDate() < dob.getDate()) months--;
  if (months < 0) months = 0;
  return months + ' meses';
}

function renderMascotasTable(mascotas) {
  const tbody = document.getElementById('mascotasBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const list = Array.isArray(mascotas) ? mascotas : Array.isArray(mascotas.data) ? mascotas.data : [];
  if (list.length === 0) {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="7" class="text-center text-body-secondary py-4">No hay mascotas registradas</td>`;
    tbody.appendChild(tr);
    return;
  }
  const csrf = getCsrfToken();
  list.forEach((m) => {
    const owner = m.user ? [m.user.nombre, m.user.apellido_paterno, m.user.apellido_materno].filter(Boolean).join(' ') : '-';
    const row = document.createElement('tr');
    row.innerHTML = `
          <td>${m.nombre ?? '-'}</td>
          <td>${m.especie ?? '-'}</td>
          <td>${m.raza ?? '-'}</td>
          <td>${formatAge(m.fecha_nacimiento)}</td>
          <td>${m.peso != null ? m.peso + ' kg' : '-'}</td>
          <td>${owner}</td>
          <td class="text-center">
            <div class="d-inline-flex gap-1">
              <a href="/mascotas/${m.id}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Ver">
                <i class="bi bi-eye"></i>
              </a>
              <a href="/mascotas/${m.id}/editar" class="btn btn-info btn-sm text-white" data-bs-toggle="tooltip" title="Editar">
                <i class="bi bi-pencil-square"></i>
              </a>
              <form method="POST" action="/mascotas/${m.id}" onsubmit="return confirm('¿Seguro que deseas eliminar esta mascota?')">
                <input type="hidden" name="_method" value="DELETE" />
                <input type="hidden" name="_token" value="${csrf}" />
                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
        `;
    tbody.appendChild(row);
  });
}

function renderMascotasPagination(meta) {
  const pag = document.getElementById('mascotasPagination');
  if (!pag) return;
  pag.innerHTML = '';
  if (!meta || meta.last_page <= 1) return;
  const ul = document.createElement('ul');
  ul.className = 'pagination m-0';
  const addItem = (label, page, disabled = false, active = false) => {
    const li = document.createElement('li');
    li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;
    const a = document.createElement('a');
    a.className = 'page-link';
    a.href = '#';
    a.textContent = label;
    a.addEventListener('click', (e) => {
      e.preventDefault();
      if (!disabled && !active) fetchMascotasAndRender(page);
    });
    li.appendChild(a);
    ul.appendChild(li);
  };
  addItem('«', meta.current_page - 1, meta.current_page === 1);
  for (let p = 1; p <= meta.last_page; p++) addItem(String(p), p, false, p === meta.current_page);
  addItem('»', meta.current_page + 1, meta.current_page === meta.last_page);
  pag.appendChild(ul);
}

function fetchMascotasAndRender(page = 1, perPage = 10) {
  const q = document.getElementById('mascotasSearch')?.value?.trim() || '';
  const scope = document.querySelector('input[name="mascotasScope"]:checked')?.value || 'today';
  const from = document.getElementById('mascotasFrom')?.value || '';
  const to = document.getElementById('mascotasTo')?.value || '';
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage), scope });
  if (q) params.set('q', q);
  if (from) params.set('from', from);
  if (to) params.set('to', to);
  const url = `/mascotas/json?${params.toString()}`;
  fetch(url, { headers: { Accept: 'application/json' } })
    .then((res) => {
      if (!res.ok) {
        throw new Error('HTTP ' + res.status);
      }
      return res.json();
    })
    .then((data) => {
      console.log('Mascotas cargadas:', data);
      renderMascotasTable(data);
      const meta = {
        current_page: data.current_page,
        last_page: data.last_page,
      };
      renderMascotasPagination(meta);
    })
    .catch((err) => console.error('Error al cargar mascotas:', err));
}

function markActive(section) {
  document.querySelectorAll('.nav-btn').forEach((b) => b.classList.remove('active'));
  const btn = document.querySelector(`.nav-btn [data-section="${section}"]`)?.closest('.nav-btn') ||
    document.querySelector(`.nav-btn[data-section="${section}"]`);
  if (btn) btn.classList.add('active');
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
        <h1 class="mb-1">¡Bienvenida, ${userName}! 👋</h1>
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
              <div class="icon-bubble bg-opacity-25 bg-primary-subtle text-primary"><i class="bi bi-calendar3"></i></div>
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
              <div class="icon-bubble bg-opacity-25 bg-success text-success"><i class="bi bi-check-circle"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Mascotas</div>
                <div class="h4 mb-1" id="mascotasRegistradas">0</div>
                <div class="small text-success" id="porcentajeMascotasRegistradas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-info text-info"><i class="bi bi-heart"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Clientes Nuevos</div>
                <div class="h4 mb-1" id="clientesNuevos">0</div>
                <div class="small text-success" id="porcentajeClientesNuevos">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-warning text-warning"><i class="bi bi-person-plus"></i></div>
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
            <h3 class="h6 mb-3">Actividad Reciente</h3>
            <div class="vstack gap-3" id="actividadReciente">
              "No hay actividad reciente"

            </div>
          </div>
        </div>
      </div>
      <div class="card card-soft p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h3 class="h6 mb-0">Citas de Hoy - <span id="todayDate">17/10/2025</span></h3>
            <div class="small text-body-secondary">6 citas</div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>HORA</th>
                <th>MASCOTA</th>
                <th>DUEÑO</th>
                <th>RAZA</th>
                <th>MOTIVO</th>
                <th>CLÍNICA</th>
                <th>ESTADO</th>
              </tr>
            </thead>
            <tbody id="appointmentsBody"></tbody>
          </table>
        </div>
      </div>
    `;
  } else if (section === 'clientes') {
    // TODO: render clientes
  } else if (section === 'mascotas') {
    mainContent.innerHTML = `
      <div class="card shadow-sm mt-4">
        <div class="card-body">
          <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-3">
            <div class="input-group" style="max-width: 420px;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input id="mascotasSearch" type="text" class="form-control" placeholder="Buscar por nombre, especie, raza o dueño">
              <button id="mascotasSearchBtn" class="btn btn-primary">Buscar</button>
            </div>
            <div class="d-flex flex-column flex-md-row gap-2">
              <div class="btn-group" role="group" aria-label="ScopeMascotas">
                <input type="radio" class="btn-check" name="mascotasScope" id="mScopeToday" autocomplete="off" value="today" checked>
                <label class="btn btn-outline-secondary" for="mScopeToday">Registradas hoy</label>
                <input type="radio" class="btn-check" name="mascotasScope" id="mScopePast" autocomplete="off" value="past">
                <label class="btn btn-outline-secondary" for="mScopePast">Anteriores</label>
              </div>
              <div class="input-group" style="max-width: 360px;">
                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                <input type="date" id="mascotasFrom" class="form-control" placeholder="Desde">
                <input type="date" id="mascotasTo" class="form-control" placeholder="Hasta">
                <button id="mascotasRangeBtn" class="btn btn-outline-primary">Filtrar</button>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>Nombre</th>
                  <th>Especie</th>
                  <th>Raza</th>
                  <th>Edad</th>
                  <th>Peso</th>
                  <th>Dueño</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody id="mascotasBody"></tbody>
            </table>
          </div>
          <div id="mascotasPagination" class="d-flex justify-content-center my-3"></div>
        </div>
      </div>
    `;
    // eventos y carga inicial
    const mSearchBtn = document.getElementById('mascotasSearchBtn');
    const mRangeBtn = document.getElementById('mascotasRangeBtn');
    const mSearchInput = document.getElementById('mascotasSearch');
    const mRadios = document.querySelectorAll('input[name="mascotasScope"]');
    mSearchBtn?.addEventListener('click', () => fetchMascotasAndRender(1));
    mRangeBtn?.addEventListener('click', () => fetchMascotasAndRender(1));
    mSearchInput?.addEventListener('keydown', (e) => { if (e.key === 'Enter') fetchMascotasAndRender(1); });
    mRadios.forEach(r => r.addEventListener('change', () => fetchMascotasAndRender(1)));
    fetchMascotasAndRender();
  } else if (section === 'citas') {
    mainContent.innerHTML = `
      <div class="card shadow-sm mt-4">
        <div class="card-body">
          <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-3">
            <div class="input-group" style="max-width: 420px;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input id="citasSearch" type="text" class="form-control" placeholder="Buscar por clínica, servicio, mascota o propietario">
              <button id="citasSearchBtn" class="btn btn-primary">Buscar</button>
            </div>
            <div class="d-flex flex-column flex-md-row gap-2">
              <div class="btn-group" role="group" aria-label="Scope">
                <input type="radio" class="btn-check" name="citasScope" id="scopeToday" autocomplete="off" value="today" checked>
                <label class="btn btn-outline-secondary" for="scopeToday">Hoy</label>
                <input type="radio" class="btn-check" name="citasScope" id="scopePast" autocomplete="off" value="past">
                <label class="btn btn-outline-secondary" for="scopePast">Pasadas</label>
              </div>
              <div class="input-group" style="max-width: 360px;">
                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                <input type="date" id="citasFrom" class="form-control" placeholder="Desde">
                <input type="date" id="citasTo" class="form-control" placeholder="Hasta">
                <button id="citasRangeBtn" class="btn btn-outline-primary">Filtrar</button>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Fecha</th>
                  <th>Clínica</th>
                  <th>Servicio</th>
                  <th>Mascota</th>
                  <th>Propietario</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody id="citasBody"></tbody>
            </table>
          </div>
          <div id="citasPagination" class="d-flex justify-content-center my-3"></div>
        </div>
      </div>
    `;
    // eventos
    const searchBtn = document.getElementById('citasSearchBtn');
    const rangeBtn = document.getElementById('citasRangeBtn');
    const searchInput = document.getElementById('citasSearch');
    const radios = document.querySelectorAll('input[name="citasScope"]');
    searchBtn?.addEventListener('click', () => fetchCitasAndRender(1));
    rangeBtn?.addEventListener('click', () => fetchCitasAndRender(1));
    searchInput?.addEventListener('keydown', (e) => { if (e.key === 'Enter') fetchCitasAndRender(1); });
    radios.forEach(r => r.addEventListener('change', () => fetchCitasAndRender(1)));
    // carga inicial
    fetchCitasAndRender();
  } else if (section === 'trabajadores') {
    // TODO: render trabajadores
  } else if (section === 'reportes') {
    // TODO: render reportes
  } else if (section === 'configuracion') {
    // TODO: render configuracion
  }
}

function initNavHandlers() {
  document.querySelectorAll('.nav-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const section = btn.dataset.section || btn.querySelector('[data-section]')?.dataset.section;
      if (!section) return;
      document.querySelectorAll('.nav-btn').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      if (section === 'home') {
        fetch('/dashboard/data')
          .then((res) => res.json())
          .then((data) => {
            renderSection('home', data);
            const { labels, data: series } = buildChartSeries(data.citasPorDia);
            updateDashboardMetrics(data);
            renderChart(labels, series);
            renderActividades(data.actividades);
            renderAppointments(data.citas);
            setTodayTexts();
            history.pushState({ section: 'home' }, '', '/dashboard/home');
          });
        return;
      }

      if (section === 'mascotas') {
        renderSection('mascotas');
        history.pushState({ section: 'mascotas' }, '', '/dashboard/mascotas');
        return;
      }

      if (section === 'citas') {
        renderSection('citas');
        fetchCitasAndRender();
        history.pushState({ section: 'citas' }, '', '/dashboard/citas');
        return;
      }

      renderSection(section);
      history.pushState({ section }, '', `/dashboard/${section}`);
    });
  });
}

function handlePopState() {
  window.addEventListener('popstate', (event) => {
    const section = event.state?.section || (location.pathname.split('/')[2] || 'home');
    markActive(section);
    if (section === 'home') {
      fetch('/dashboard/data')
        .then((res) => res.json())
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeries(data.citasPorDia);
          updateDashboardMetrics(data);
          renderChart(labels, series);
          renderAppointments(data.citas);
          renderActividades(data.actividades);
          setTodayTexts();
        });
    } else if (section === 'mascotas') {
      renderSection('mascotas');
      fetchMascotasAndRender();
    } else if (section === 'citas') {
      renderSection('citas');
      fetchCitasAndRender();
    } else {
      renderSection(section);
    }
  });
}

// Bootstrap de la página
(function() {
  document.addEventListener('DOMContentLoaded', () => {
    const match = location.pathname.match(/^\/dashboard(?:\/([^\/?#]+))?/);
    const initialSection = (match && match[1]) ? match[1] : 'home';
    markActive(initialSection);

    if (initialSection === 'home') {
      fetch('/dashboard/data')
        .then((r) => r.json())
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeries(data.citasPorDia);
          updateDashboardMetrics(data);
          renderChart(labels, series);
          renderActividades(data.actividades);
          renderAppointments(data.citas);
          setTodayTexts();
          history.replaceState({ section: 'home' }, '', location.pathname);
        })
        .catch((err) => console.error('Error al obtener los datos del dashboard:', err));
    } else if (initialSection === 'mascotas') {
      renderSection('mascotas');
      fetchMascotasAndRender();
      history.replaceState({ section: 'mascotas' }, '', location.pathname);
    } else if (initialSection === 'citas') {
      renderSection('citas');
      fetchCitasAndRender();
      history.replaceState({ section: 'citas' }, '', location.pathname);
    } else {
      renderSection(initialSection);
      history.replaceState({ section: initialSection }, '', location.pathname);
    }

    initNavHandlers();
    handlePopState();
  });
})();

// ---- Citas: helpers y fetch ----
function renderCitasTable(items) {
  const tbody = document.getElementById('citasBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const list = Array.isArray(items) ? items : (Array.isArray(items.data) ? items.data : []);
  if (list.length === 0) {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="6" class="text-center text-body-secondary py-4">No hay citas</td>`;
    tbody.appendChild(tr);
    return;
  }
  list.forEach((c) => {
    const fecha = c.fecha ? new Date(c.fecha) : null;
    const fmt = (d) => (d ? `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}` : '-');
    const propietario = c.mascota?.user ? [c.mascota.user.nombre, c.mascota.user.apellido_paterno, c.mascota.user.apellido_materno].filter(Boolean).join(' ') : '-';
    const badge = ((s) => {
      if (s === 'pendiente') return '<span class="badge bg-warning text-dark">Pendiente</span>';
      if (s === 'completada') return '<span class="badge bg-success">Completada</span>';
      if (s === 'cancelada') return '<span class="badge bg-danger">Cancelada</span>';
      return '<span class="badge bg-secondary">-</span>';
    })(c.status);
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${fmt(fecha)}</td>
      <td>${c.clinica?.nombre ?? '-'}</td>
      <td>${c.servicio?.nombre ?? '-'}</td>
      <td>${c.mascota?.nombre ?? '-'}</td>
      <td>${propietario}</td>
      <td>${badge}</td>
    `;
    tbody.appendChild(tr);
  });
}

function renderCitasPagination(meta) {
  const pag = document.getElementById('citasPagination');
  if (!pag) return;
  pag.innerHTML = '';
  if (!meta || meta.last_page <= 1) return;
  const ul = document.createElement('ul');
  ul.className = 'pagination m-0';
  const addItem = (label, page, disabled = false, active = false) => {
    const li = document.createElement('li');
    li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;
    const a = document.createElement('a');
    a.className = 'page-link';
    a.href = '#';
    a.textContent = label;
    a.addEventListener('click', (e) => {
      e.preventDefault();
      if (!disabled && !active) fetchCitasAndRender(page);
    });
    li.appendChild(a);
    ul.appendChild(li);
  };
  addItem('«', meta.current_page - 1, meta.current_page === 1);
  for (let p = 1; p <= meta.last_page; p++) addItem(String(p), p, false, p === meta.current_page);
  addItem('»', meta.current_page + 1, meta.current_page === meta.last_page);
  pag.appendChild(ul);
}

function fetchCitasAndRender(page = 1, perPage = 10) {
  const q = document.getElementById('citasSearch')?.value?.trim() || '';
  const scope = document.querySelector('input[name="citasScope"]:checked')?.value || 'today';
  const from = document.getElementById('citasFrom')?.value || '';
  const to = document.getElementById('citasTo')?.value || '';
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage), scope });
  if (q) params.set('q', q);
  if (from) params.set('from', from);
  if (to) params.set('to', to);
  fetch(`/citas/json?${params.toString()}`, { headers: { Accept: 'application/json' } })
    .then((r) => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then((data) => {
      renderCitasTable(data);
      renderCitasPagination({ current_page: data.current_page, last_page: data.last_page });
    })
    .catch((err) => console.error('Error al cargar citas:', err));
}
