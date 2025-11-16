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

  if (!actividades || actividades.length === 0) {
    // Mensaje cuando no hay registros
    const mensaje = document.createElement('div');
    mensaje.className = 'text-center text-body-secondary py-3';
    mensaje.textContent = 'No hay actividad reciente';
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

/**
 * Render a simple skeleton placeholder for lists/tables while data is loading.
 * section: 'mascotas' | 'citas' | 'home' (partial support)
 * rows: number of placeholder rows to render for table-like sections
 */
function renderSkeleton(section, rows = 5) {
  if (section === 'mascotas') {
    const tbody = document.getElementById('mascotasBody');
    const pag = document.getElementById('mascotasPagination');
    if (tbody) {
      tbody.innerHTML = '';
      for (let i = 0; i < rows; i++) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><div class="placeholder-glow"><span class="placeholder col-6"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-4"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-4"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-3"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-3"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-6"></span></div></td>
          <td class="text-center"><div class="placeholder-glow"><span class="placeholder col-2"></span></div></td>
        `;
        tbody.appendChild(tr);
      }
    }
    if (pag) pag.innerHTML = '';
    return;
  }

  if (section === 'citas') {
    const tbody = document.getElementById('citasBody');
    const pag = document.getElementById('citasPagination');
    if (tbody) {
      tbody.innerHTML = '';
      for (let i = 0; i < rows; i++) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><div class="placeholder-glow"><span class="placeholder col-6"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-5"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-4"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-4"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-6"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-2"></span></div></td>
        `;
        tbody.appendChild(tr);
      }
    }
    if (pag) pag.innerHTML = '';
    return;
  }

  if (section === 'clientes') {
    const tbody = document.getElementById('clientesBody');
    const pag = document.getElementById('clientesPagination');
    if (tbody) {
      tbody.innerHTML = '';
      for (let i = 0; i < rows; i++) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><div class="placeholder-glow"><span class="placeholder col-6"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-5"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-3"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-2"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-6"></span></div></td>
          <td><div class="placeholder-glow"><span class="placeholder col-4"></span></div></td>
          <td class="text-center"><div class="placeholder-glow"><span class="placeholder col-2"></span></div></td>`;
        tbody.appendChild(tr);
      }
    }
    if (pag) pag.innerHTML = '';
    return;
  }

  if (section === 'home') {
    const chart = document.getElementById('appointmentsChart');
    const actividad = document.getElementById('actividadReciente');
    if (chart) {
      // replace canvas with a simple placeholder box
      const parent = chart.parentElement;
      if (parent) {
        parent.innerHTML = '<div class="bg-light border" style="height:220px;border-radius:6px;"></div>';
      }
    }
    if (actividad) actividad.innerHTML = '<div class="text-center text-body-secondary py-3">Cargando actividad...</div>';
  }
}

function fetchMascotasAndRender(page = 1, perPage = 10) {
  // show skeleton while loading
  renderSkeleton('mascotas', 6);
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

// ---- Clientes: helpers y fetch ----
function renderClientesTable(items) {
  const tbody = document.getElementById('clientesBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const list = Array.isArray(items) ? items : (Array.isArray(items.data) ? items.data : []);
  if (list.length === 0) {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="7" class="text-center text-body-secondary py-4">No hay clientes</td>`;
    tbody.appendChild(tr);
    return;
  }
  const csrf = getCsrfToken();
  list.forEach(u => {
    const apellido = [u.apellido_paterno, u.apellido_materno].filter(Boolean).join(' ');
    const generoMap = { M: 'Masculino', F: 'Femenino', O: 'Otro' };
    const genero = generoMap[u.genero] || (u.genero || '-');
    const edad = formatAge(u.fecha_nacimiento);
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${u.nombre ?? '-'}</td>
      <td>${apellido || '-'}</td>
      <td>${genero}</td>
      <td>${edad}</td>
      <td>${u.email ?? '-'}</td>
      <td>${u.telefono ?? '-'}</td>
      <td class="text-center">
        <div class="d-inline-flex gap-1">
          <a href="/usuarios/${u.id}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Ver"><i class="bi bi-eye"></i></a>
          <a href="/usuarios/${u.id}/editar" class="btn btn-info btn-sm text-white" data-bs-toggle="tooltip" title="Editar"><i class="bi bi-pencil-square"></i></a>
          <form method="POST" action="/usuarios/${u.id}" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')">
            <input type="hidden" name="_method" value="DELETE" />
            <input type="hidden" name="_token" value="${csrf}" />
            <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </td>`;
    tbody.appendChild(tr);
  });
}

function renderClientesPagination(meta) {
  const pag = document.getElementById('clientesPagination');
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
      if (!disabled && !active) fetchClientesAndRender(page);
    });
    li.appendChild(a);
    ul.appendChild(li);
  };
  addItem('«', meta.current_page - 1, meta.current_page === 1);
  for (let p = 1; p <= meta.last_page; p++) addItem(String(p), p, false, p === meta.current_page);
  addItem('»', meta.current_page + 1, meta.current_page === meta.last_page);
  pag.appendChild(ul);
}

function fetchClientesAndRender(page = 1, perPage = 10) {
  renderSkeleton('clientes', 6);
  const q = document.getElementById('clientesSearch')?.value?.trim() || '';
  const scope = document.querySelector('input[name="clientesScope"]:checked')?.value || 'today';
  const from = document.getElementById('clientesFrom')?.value || '';
  const to = document.getElementById('clientesTo')?.value || '';
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage), scope });
  if (q) params.set('q', q);
  if (from) params.set('from', from);
  if (to) params.set('to', to);
  fetch(`/usuarios/json?${params.toString()}`, { headers: { Accept: 'application/json' } })
    .then(async r => {
      if (!r.ok) {
        const text = await r.text();
        throw new Error('HTTP ' + r.status + ' ' + text.substring(0, 120));
      }
      return r.json();
    })
    .then(data => {
      renderClientesTable(data);
      renderClientesPagination({ current_page: data.current_page, last_page: data.last_page });
    })
    .catch(err => {
      console.error('Error al cargar clientes:', err);
      const tbody = document.getElementById('clientesBody');
      if (tbody) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar clientes. Intenta nuevamente.</td></tr>`;
      }
    });
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
    mainContent.innerHTML = `
    <div class="card shadow-sm mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Clientes</h5>
        <a href="/clientes/crear" class="btn btn-success">
          <i class="bi bi-plus-lg me-1"></i> Agregar Cliente
        </a>
      </div>

      <div class="card-body">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-3">
          <div class="input-group" style="max-width: 420px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="clientesSearch" type="text" class="form-control" placeholder="Buscar por nombre o apellido">
            <button id="clientesSearchBtn" class="btn btn-primary">Buscar</button>
          </div>

          <div class="d-flex flex-column flex-md-row gap-2">
            <div class="btn-group" role="group" aria-label="ScopeMascotas">
              <input type="radio" class="btn-check" name="clientesScope" id="cScopeToday" autocomplete="off" value="today" checked>
              <label class="btn btn-outline-secondary" for="cScopeToday">Registrados hoy</label>
              <input type="radio" class="btn-check" name="clientesScope" id="cScopePast" autocomplete="off" value="past">
              <label class="btn btn-outline-secondary" for="cScopePast">Anteriores</label>
            </div>

            <div class="input-group" style="max-width: 360px;">
              <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
              <input type="date" id="clientesFrom" class="form-control" placeholder="Desde">
              <input type="date" id="clientesTo" class="form-control" placeholder="Hasta">
              <button id="clientesRangeBtn" class="btn btn-outline-primary">Filtrar</button>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Genero</th>
                <th>Edad</th>
                <th>Email</th>
                <th>Telefono</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody id="clientesBody"></tbody>
          </table>
        </div>

        <div id="clientesPagination" class="d-flex justify-content-center my-3"></div>
      </div>
    </div>

    `;
    // eventos y carga inicial clientes
    const cSearchBtn = document.getElementById('clientesSearchBtn');
    const cRangeBtn = document.getElementById('clientesRangeBtn');
    const cSearchInput = document.getElementById('clientesSearch');
    const cRadios = document.querySelectorAll('input[name="clientesScope"]');
    cSearchBtn?.addEventListener('click', () => fetchClientesAndRender(1));
    cRangeBtn?.addEventListener('click', () => fetchClientesAndRender(1));
    cSearchInput?.addEventListener('keydown', (e) => { if (e.key === 'Enter') fetchClientesAndRender(1); });
    cRadios.forEach(r => r.addEventListener('change', () => fetchClientesAndRender(1)));
    fetchClientesAndRender();
  } else if (section === 'mascotas') {
    mainContent.innerHTML = `
    <div class="card shadow-sm mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Mascotas</h5>
        <a href="/mascotas/crear" class="btn btn-success">
          <i class="bi bi-plus-lg me-1"></i> Agregar Mascota
        </a>
      </div>

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
    // Render de la sección Reportes dentro del SPA
    mainContent.innerHTML = `
      <style>
        .text-purple{ color:#6f42c1 !important; }
        .metric-card{ border:1px solid #e9ecef; border-radius: var(--radius-xl); }
        .metric-card .icon-bubble{ width:46px; height:46px; border-radius: .75rem; display:flex; align-items:center; justify-content:center; background: rgba(111,66,193,.12); color:#6f42c1; }
        .skeleton{ position:relative; overflow:hidden; background:#e9ecef; color:transparent !important; border-radius:.25rem; }
        .skeleton::after{ content:""; position:absolute; inset:0; transform:translateX(-100%); background:linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent); animation:shimmer 1.2s infinite; }
        @keyframes shimmer{ 100%{ transform:translateX(100%);} }
        .chart-wrap{ position:relative; height:280px; }
        .chart-wrap canvas{ width:100% !important; height:100% !important; }
        .chart-spinner{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:transparent; }
        .table thead th{ font-weight:600; }
      </style>
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <h2 class="mb-1">Reportes</h2>
          <div class="text-body-secondary">Analiza el rendimiento por fechas, servicios y personal.</div>
        </div>
        <div class="small text-body-secondary" id="texto-rango"></div>
      </div>

      <div class="card shadow-sm mt-4 p-3">
        <div class="row align-items-end">
          <div class="col-md-2">
            <label class="form-label">Rango de fechas:</label>
            <select id="filtro-rango" class="form-select">
              <option value="mes-actual" selected>Este mes</option>
              <option value="mes-anterior">Último mes</option>
              <option value="3-meses">Últimos 3 meses</option>
              <option value="custom">Personalizado</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Desde:</label>
            <input id="filtro-desde" type="date" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">Hasta:</label>
            <input id="filtro-hasta" type="date" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">Rol:</label>
            <select id="filtro-rol" class="form-select">
              <option value="">Todos</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Trabajador (ID):</label>
            <input id="filtro-trabajador" type="number" class="form-control" placeholder="Ej. 12">
          </div>
          <div class="col-md-2 d-flex justify-content-end gap-2">
            <button id="btn-aplicar-filtro" class="btn text-white" style="background:#6f42c1;"><i class="bi bi-funnel"></i> Aplicar filtro</button>
            <div class="btn-group">
              <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download"></i> Exportar
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" id="btn-exportar-xlsx">Exportar XLSX</a></li>
                <li><a class="dropdown-item" href="#" id="btn-exportar-pdf">Exportar PDF</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-3">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-calendar-check"></i></div></div>
            <h4 id="metric-citas-realizadas" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Citas realizadas</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-heart-pulse"></i></div></div>
            <h4 id="metric-mascotas-atendidas" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Mascotas atendidas</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-person-check"></i></div></div>
            <h4 id="metric-clientes-nuevos" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Clientes nuevos</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="metric-card p-3 shadow-sm text-center">
            <div class="d-flex justify-content-center"><div class="icon-bubble"><i class="bi bi-cash-stack"></i></div></div>
            <h4 id="metric-ingresos-totales" class="mt-2 mb-0">—</h4>
            <div class="text-body-secondary">Ingresos totales</div>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold mb-2">Citas por servicio</h6>
            <div class="chart-wrap">
              <div id="spinner-citas" class="chart-spinner d-none"><div class="spinner-border" role="status"></div></div>
              <canvas id="chartCitas"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold mb-2">Mascotas por especie</h6>
            <div class="chart-wrap">
              <div id="spinner-mascotas" class="chart-spinner d-none"><div class="spinner-border" role="status"></div></div>
              <canvas id="chartMascotas"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold mb-2">Ingresos mensuales</h6>
            <div class="chart-wrap">
              <div id="spinner-ingresos" class="chart-spinner d-none"><div class="spinner-border" role="status"></div></div>
              <canvas id="chartIngresos"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3">
            <h6 class="fw-bold mb-2">Servicios más solicitados</h6>
            <div class="chart-wrap">
              <div id="spinner-servicios" class="chart-spinner d-none"><div class="spinner-border" role="status"></div></div>
              <canvas id="chartProductos"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Resumen de citas</span>
          <a href="#">Ver todos</a>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Estado</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
                <th>Tendencia</th>
              </tr>
            </thead>
            <tbody id="tabla-resumen-citas">
              <tr class="placeholder-row"><td colspan="4" class="text-center text-body-secondary">Cargando…</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm mt-4 mb-5">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Servicios más solicitados</span>
          <a href="#">Ver todos</a>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Servicio</th>
                <th>Cantidad</th>
                <th>Ingresos</th>
                <th>Variación</th>
              </tr>
            </thead>
            <tbody id="tabla-servicios-top">
              <tr class="placeholder-row"><td colspan="4" class="text-center text-body-secondary">Cargando…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    `;

    // Cargar el script de reportes una sola vez y ejecutar
    (function loadReportesOnce(){
      if (window.__reportesLoaded) return;
      const existing = document.querySelector('script[src*="/js/reportes.js"]');
      if (existing) { window.__reportesLoaded = true; return; }
      const s = document.createElement('script');
      s.src = '/js/reportes.js';
      s.onload = () => { window.__reportesLoaded = true; };
      document.body.appendChild(s);
    })();
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

      if (section === 'clientes') {
        renderSection('clientes');
        history.pushState({ section: 'clientes' }, '', '/dashboard/clientes');
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
    } else if (section === 'clientes') {
      renderSection('clientes');
      fetchClientesAndRender();
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
  // show skeleton while loading
  renderSkeleton('citas', 6);
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
