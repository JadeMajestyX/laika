(() => {
  const $ = (sel) => document.querySelector(sel);

  const ui = {
    rango: $('#filtro-rango'),
    desde: $('#filtro-desde'),
    hasta: $('#filtro-hasta'),
    rol: $('#filtro-rol'), // actualmente no se filtra por rol en backend
    trabajador: $('#filtro-trabajador'),
    btnAplicar: $('#btn-aplicar-filtro'),
  btnExportarXlsx: $('#btn-exportar-xlsx'),
  btnExportarPdf: $('#btn-exportar-pdf'),
    mCitas: $('#metric-citas-realizadas'),
    mMascotas: $('#metric-mascotas-atendidas'),
    mClientes: $('#metric-clientes-nuevos'),
    mIngresos: $('#metric-ingresos-totales'),
    tablaResumenCitas: $('#tabla-resumen-citas'),
    tablaServiciosTop: $('#tabla-servicios-top'),
    spinnerCitas: $('#spinner-citas'),
    spinnerMascotas: $('#spinner-mascotas'),
    spinnerIngresos: $('#spinner-ingresos'),
    spinnerServicios: $('#spinner-servicios'),
    textoRango: document.getElementById('texto-rango'),
  };

  // Charts
  let chartCitas = null;
  let chartMascotas = null;
  let chartIngresos = null;
  let chartServicios = null; // usamos el canvas de 'chartProductos'

  function formatCurrency(num) {
    const n = Number(num || 0);
    return n.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
  }

  function setDefaultDatesForRange() {
    const today = new Date();
    const y = today.getFullYear();
    const m = today.getMonth(); // 0-11

    let from, to;
    switch (ui.rango.value) {
      case 'mes-anterior': {
        const firstPrev = new Date(y, m - 1, 1);
        const lastPrev = new Date(y, m, 0);
        from = firstPrev; to = lastPrev; break;
      }
      case '3-meses': {
        const start = new Date(y, m - 2, 1);
        const end = new Date(y, m + 1, 0);
        from = start; to = end; break;
      }
      case 'custom': {
        // no sobreescribir si el usuario quiere personalizar
        return;
      }
      case 'mes-actual':
      default: {
        const first = new Date(y, m, 1);
        const last = new Date(y, m + 1, 0);
        from = first; to = last; break;
      }
    }

    ui.desde.value = toDateInput(from);
    ui.hasta.value = toDateInput(to);
  }

  function toDateInput(d) {
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  function buildQuery() {
    const params = new URLSearchParams();
    if (ui.desde.value) params.set('from', ui.desde.value);
    if (ui.hasta.value) params.set('to', ui.hasta.value);
    const trabajadorId = ui.trabajador.value?.trim();
    if (trabajadorId) params.set('trabajador_id', trabajadorId);
    const rol = ui.rol?.value?.trim();
    if (rol) params.set('rol', rol);
    return params.toString();
  }

  async function loadData() {
    setLoading(true);
    const qs = buildQuery();
    const url = `/reportes/data${qs ? `?${qs}` : ''}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error('Error al cargar datos de reportes');
    const data = await res.json();
    renderAll(data);
    setLoading(false);
  }

  function renderAll(data) {
    // Poblar selector de roles si viene en la respuesta y aún no está poblado
    try {
      if (ui.rol && Array.isArray(data?.roles)) {
        const hasDynamic = ui.rol.options && ui.rol.options.length > 1;
        if (!hasDynamic) {
          // limpiar excepto "Todos"
          const keepFirst = ui.rol.querySelector('option[value=""]');
          ui.rol.innerHTML = '';
          const optAll = document.createElement('option');
          optAll.value = '';
          optAll.textContent = 'Todos';
          ui.rol.appendChild(optAll);
          data.roles.forEach((r) => {
            if (!r) return;
            const opt = document.createElement('option');
            opt.value = r;
            opt.textContent = String(r).charAt(0).toUpperCase() + String(r).slice(1);
            ui.rol.appendChild(opt);
          });
        }
      }
    } catch (_) {}

    // Métricas
    ui.mCitas.textContent = data?.metrics?.citas_realizadas ?? '0';
    ui.mMascotas.textContent = data?.metrics?.mascotas_atendidas ?? '0';
    ui.mClientes.textContent = data?.metrics?.clientes_nuevos ?? '0';
    ui.mIngresos.textContent = formatCurrency(data?.metrics?.ingresos_totales ?? 0);

    // Gráficas
    renderCharts(data);

    // Tablas
    renderTables(data);

    // Texto de rango
    try {
      if (ui.textoRango && data?.filters) {
        const { from, to, rol, trabajador_id } = data.filters;
        let extras = [];
        if (rol) extras.push(`Rol: ${rol}`);
        if (trabajador_id) extras.push(`Trabajador: ${trabajador_id}`);
        ui.textoRango.textContent = `${from ?? ''} — ${to ?? ''}${extras.length ? ' · ' + extras.join(' · ') : ''}`;
      }
    } catch {}
  }

  function renderCharts(data) {
    // Citas por servicio (pie)
    const citas = (data?.charts?.citas_por_servicio || []);
    const citasLabels = citas.map(r => r.label || 'N/D');
    const citasValues = citas.map(r => r.value || 0);
    const citasColors = ['#6f42c1','#dc3545','#0d6efd','#20c997','#ffc107','#198754','#fd7e14'];

    chartCitas?.destroy();
    if (window.Chart) chartCitas = new Chart(document.getElementById('chartCitas'), {
      type: 'pie',
      data: {
        labels: citasLabels,
        datasets: [{ data: citasValues, backgroundColor: citasLabels.map((_,i)=>citasColors[i%citasColors.length]) }]
      }
    });

    // Mascotas por especie (pie)
    const esp = (data?.charts?.mascotas_por_especie || []);
    const espLabels = esp.map(r => r.label || 'N/D');
    const espValues = esp.map(r => r.value || 0);

    chartMascotas?.destroy();
  if (window.Chart) chartMascotas = new Chart(document.getElementById('chartMascotas'), {
      type: 'pie',
      data: {
        labels: espLabels,
        datasets: [{ data: espValues, backgroundColor: espLabels.map((_,i)=>citasColors[i%citasColors.length]) }]
      }
    });

    // Ingresos mensuales (bar)
    const ing = (data?.charts?.ingresos_mensuales || []);
    const ingLabels = ing.map(r => r.month);
    const ingValues = ing.map(r => Number(r.total || 0));

    chartIngresos?.destroy();
  if (window.Chart) chartIngresos = new Chart(document.getElementById('chartIngresos'), {
      type: 'bar',
      data: {
        labels: ingLabels,
        datasets: [{ label: 'Ingresos en $', data: ingValues, backgroundColor: '#6f42c1' }]
      }
    });

    // Servicios top como gráfico horizontal (usamos chartProductos)
    const top = (data?.charts?.servicios_top || []);
    const topLabels = top.map(r => r.label);
    const topValues = top.map(r => r.cantidad);

    chartServicios?.destroy();
  if (window.Chart) chartServicios = new Chart(document.getElementById('chartProductos'), {
      type: 'bar',
      data: {
        labels: topLabels,
        datasets: [{ label: 'Servicios (cantidad)', data: topValues, backgroundColor: '#28a745' }]
      },
      options: { indexAxis: 'y' }
    });
  }

  function setLoading(isLoading) {
    const toggle = (el, show) => { if (!el) return; el.classList.toggle('d-none', !show); };
    // Spinners de charts
    toggle(ui.spinnerCitas, isLoading);
    toggle(ui.spinnerMascotas, isLoading);
    toggle(ui.spinnerIngresos, isLoading);
    toggle(ui.spinnerServicios, isLoading);
    // Métricas skeleton
    ;[ui.mCitas, ui.mMascotas, ui.mClientes, ui.mIngresos].forEach(el => {
      if (!el) return;
      if (isLoading) { el.classList.add('skeleton'); el.textContent = '···'; }
      else { el.classList.remove('skeleton'); }
    });
    // Tablas placeholders
    if (isLoading) {
      if (ui.tablaResumenCitas && !ui.tablaResumenCitas.querySelector('.placeholder-row')) {
        ui.tablaResumenCitas.innerHTML = '<tr class="placeholder-row"><td colspan="4" class="text-center text-body-secondary">Cargando…</td></tr>';
      }
      if (ui.tablaServiciosTop && !ui.tablaServiciosTop.querySelector('.placeholder-row')) {
        ui.tablaServiciosTop.innerHTML = '<tr class="placeholder-row"><td colspan="4" class="text-center text-body-secondary">Cargando…</td></tr>';
      }
    }
  }

  function renderTables(data) {
    // Resumen de citas por status
    const tbody = ui.tablaResumenCitas;
    tbody.innerHTML = '';
    const resumen = data?.resumen_citas || [];
    if (!resumen.length) {
      tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin datos</td></tr>';
    } else {
      for (const r of resumen) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${r.label ?? '—'}</td>
          <td>${r.value ?? 0}</td>
          <td>${(r.percentage ?? 0)}%</td>
          <td class="text-muted">—</td>
        `;
        tbody.appendChild(tr);
      }
    }

    // Servicios más solicitados (tabla)
    const tbody2 = ui.tablaServiciosTop;
    tbody2.innerHTML = '';
    const top = data?.charts?.servicios_top || [];
    if (!top.length) {
      tbody2.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin datos</td></tr>';
    } else {
      for (const s of top) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${s.label ?? '—'}</td>
          <td>${s.cantidad ?? 0}</td>
          <td>${formatCurrency(s.ingresos ?? 0)}</td>
          <td class="text-muted">—</td>
        `;
        tbody2.appendChild(tr);
      }
    }
  }

  function bindEvents() {
    ui.rango?.addEventListener('change', () => {
      setDefaultDatesForRange();
    });
    ui.btnAplicar?.addEventListener('click', (e) => {
      e.preventDefault();
      loadData().catch(err => console.error(err));
    });
    ui.btnExportarXlsx?.addEventListener('click', (e) => {
      e.preventDefault();
      const qs = buildQuery();
      const url = `/reportes/citas/export/xlsx${qs ? `?${qs}` : ''}`;
      window.location.href = url;
    });
    ui.btnExportarPdf?.addEventListener('click', (e) => {
      e.preventDefault();
      const qs = buildQuery();
      const url = `/reportes/citas/export/pdf${qs ? `?${qs}` : ''}`;
      window.location.href = url;
    });
  }

  function init() {
    setDefaultDatesForRange();
    bindEvents();
    loadData().catch(err => {
      console.error(err);
      try {
        const cont = document.createElement('div');
        cont.className = 'alert alert-warning mt-3';
        cont.textContent = 'No se pudieron cargar los datos de reportes. Verifica tu sesión y filtros.';
        document.querySelector('main')?.prepend(cont);
      } catch(e) {}
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();