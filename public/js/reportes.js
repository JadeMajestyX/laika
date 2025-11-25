(() => {
  const $ = (sel) => document.querySelector(sel);

  const ui = {
    rango: $('#filtro-rango'),
    desde: $('#filtro-desde'),
    hasta: $('#filtro-hasta'),
    rol: $('#filtro-rol'), // filtro opcional por rol
    btnAplicar: $('#btn-aplicar-filtro'),
    btnExportarPdf: $('#btn-exportar-pdf'),
    mCitas: $('#metric-citas-realizadas'),
    mMascotas: $('#metric-mascotas-atendidas'),
    mClientes: $('#metric-clientes-nuevos'),
    panelCitasAtendidas: document.getElementById('panel-citas-atendidas'),
    panelUsuariosNuevos: document.getElementById('panel-usuarios-nuevos'),
    tablaResumenCitas: $('#tabla-resumen-citas'),
    textoRango: document.getElementById('texto-rango'),
  };

  const chartState = {
    resumen: null,
    metricas: null,
  };

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
    ui.mCitas.textContent = data?.metrics?.citas_atendidas ?? '0';
    ui.mMascotas.textContent = data?.metrics?.mascotas_atendidas ?? '0';
    ui.mClientes.textContent = data?.metrics?.usuarios_nuevos ?? '0';

    // Paneles adicionales
    renderPanels(data);

    // Gráficas
    renderCharts(data);

    // Tablas
    renderTables(data);

    // Texto de rango
    try {
      if (ui.textoRango && data?.filters) {
        const { from, to, rol } = data.filters;
        let extras = [];
        if (rol) extras.push(`Rol: ${rol}`);
        ui.textoRango.textContent = `${from ?? ''} — ${to ?? ''}${extras.length ? ' · ' + extras.join(' · ') : ''}`;
      }
    } catch {}
  }

  function renderPanels(data) {
    if (ui.panelCitasAtendidas) {
      ui.panelCitasAtendidas.textContent = data?.metrics?.citas_atendidas ?? '0';
    }
    if (ui.panelUsuariosNuevos) {
      ui.panelUsuariosNuevos.textContent = data?.metrics?.usuarios_nuevos ?? '0';
    }
  }

  const chartPalette = ['#6f42c1', '#0d6efd', '#20c997', '#fd7e14', '#0dcaf0', '#ffc107', '#198754', '#dc3545'];

  function destroyChart(name) {
    if (chartState[name]) {
      chartState[name].destroy();
      chartState[name] = null;
    }
  }

  function renderCharts(data) {
    if (typeof Chart === 'undefined') return;
    renderResumenChart(data?.resumen_citas || []);
    renderMetricasChart(data?.metrics || {});
  }

  function renderResumenChart(resumen) {
    const canvas = document.getElementById('chart-resumen-citas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    destroyChart('resumen');

    const hasData = Array.isArray(resumen) && resumen.length > 0;
    const labels = hasData ? resumen.map((r) => r.label ?? '—') : ['Sin datos'];
    const values = hasData ? resumen.map((r) => Number(r.value ?? 0)) : [1];
    const colors = labels.map((_, idx) => chartPalette[idx % chartPalette.length]);

    chartState.resumen = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [
          {
            data: values,
            backgroundColor: colors,
            hoverOffset: 6,
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' },
        },
      },
    });
  }

  function renderMetricasChart(metrics) {
    const canvas = document.getElementById('chart-metricas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    destroyChart('metricas');

    const labels = ['Citas atendidas', 'Mascotas atendidas', 'Usuarios nuevos'];
    const values = [
      Number(metrics?.citas_atendidas ?? 0),
      Number(metrics?.mascotas_atendidas ?? 0),
      Number(metrics?.usuarios_nuevos ?? 0),
    ];
    const colors = ['#6f42c1', '#20c997', '#0d6efd'];

    chartState.metricas = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'Total',
            data: values,
            backgroundColor: colors,
            borderRadius: 8,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#6c757d' },
          },
          y: {
            beginAtZero: true,
            grid: { color: '#f1f3f5' },
            ticks: { precision: 0, color: '#6c757d' },
          },
        },
        plugins: { legend: { display: false } },
      },
    });
  }

  function setLoading(isLoading) {
    // Métricas skeleton
    ;[ui.mCitas, ui.mMascotas, ui.mClientes].forEach(el => {
      if (!el) return;
      if (isLoading) { el.classList.add('skeleton'); el.textContent = '···'; }
      else { el.classList.remove('skeleton'); }
    });
    // Tablas placeholders
    if (isLoading) {
      if (ui.tablaResumenCitas && !ui.tablaResumenCitas.querySelector('.placeholder-row')) {
        ui.tablaResumenCitas.innerHTML = '<tr class="placeholder-row"><td colspan="4" class="text-center text-body-secondary">Cargando…</td></tr>';
      }
    }
  }

  function renderTables(data) {
    // Resumen de citas por status
    const tbody = ui.tablaResumenCitas;
    if (!tbody) return;
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
  }

  function bindEvents() {
    ui.rango?.addEventListener('change', () => {
      setDefaultDatesForRange();
    });
    ui.btnAplicar?.addEventListener('click', (e) => {
      e.preventDefault();
      loadData().catch(err => console.error(err));
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