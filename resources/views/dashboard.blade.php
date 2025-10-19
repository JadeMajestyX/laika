@extends('layouts.app_admin')

@section('title', 'VetCare - Panel')

@section('content')

  <div class="" id="mainContent">
    {{-- Se cargará dinamicamente el contenido de cada sección --}}
  </div>
@endsection

@push('scripts')
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>


  <script>
    // Helpers y carga inicial
    let chartInstance = null;
    function buildChartSeries(citasPorDia){
      const diasOrdenados = [
        { en: 'Monday', es: 'Lun' },
        { en: 'Tuesday', es: 'Mar' },
        { en: 'Wednesday', es: 'Mié' },
        { en: 'Thursday', es: 'Jue' },
        { en: 'Friday', es: 'Vie' },
        { en: 'Saturday', es: 'Sáb' },
        { en: 'Sunday', es: 'Dom' }
      ];
      const map = {};
      (citasPorDia || []).forEach(item => { map[item.dia] = item.total; });
      return {
        labels: diasOrdenados.map(d => d.es),
        data: diasOrdenados.map(d => map[d.en] || 0)
      };
    }

    function renderChart(labels, data){
      const ctx = document.getElementById('appointmentsChart');
      if(!ctx) return;
      if(chartInstance) chartInstance.destroy();
      const gridColor = getComputedStyle(document.querySelector('[data-bs-theme]'))
        .getPropertyValue('--bs-border-color').trim() || '#e9ecef';
      const barColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--brand').trim() || '#3A7CA5';
      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ label:'Citas', data, backgroundColor: barColor, borderRadius:8 }] },
        options: {
          responsive:true, maintainAspectRatio:false,
          scales:{ x:{ grid:{ display:false }, ticks:{ color:getTextColor() } }, y:{ grid:{ color:gridColor }, ticks:{ color:getTextColor() }, beginAtZero:true } },
          plugins:{ legend:{ display:false } }
        }
      });
    }

    function updateDashboardMetrics(d){
      const setVal = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
      const setPct = (id, pct) => { const el = document.getElementById(id); if(!el) return; el.textContent = (pct >= 0 ? '+' : '') + pct + '%'; el.className = 'small ' + (pct >= 0 ? 'text-success' : 'text-danger'); };
      setVal('citasHoy', d.citasHoy);
      setPct('porcentajeCitas', d.comparacionporcentaje?.citasHoy ?? 0);
      setVal('citasCompletadas', d.citasCompletadas);
      setPct('porcentajeCitasCompletadas', d.comparacionporcentaje?.citasCompletadas ?? 0);
      setVal('mascotasRegistradas', d.mascotasRegistradas);
      setPct('porcentajeMascotasRegistradas', d.comparacionporcentaje?.mascotasRegistradas ?? 0);
      setVal('clientesNuevos', d.clientesNuevos);
      setPct('porcentajeClientesNuevos', d.comparacionporcentaje?.clientesNuevos ?? 0);
    }

    function renderAppointments(appointments){
      const tbody = document.getElementById('appointmentsBody');
      if(!tbody) return;
      tbody.innerHTML = '';
      (appointments || []).forEach(apt => {
        const badge = (status => {
          switch(status){
            case 'completada': return '<span class="badge bg-success-subtle text-success border-0">Completada</span>';
            case 'confirmada': return '<span class="badge bg-warning-subtle text-warning border-0">Confirmada</span>';
            case 'pendiente': return '<span class="badge bg-primary-subtle text-primary border-0">Pendiente</span>';
            default: return '<span class="badge bg-secondary text-dark border-0">-</span>';
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

    // Helpers UI
    function markActive(section){
      document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
      const btn = document.querySelector(`.nav-btn [data-section="${section}"]`)?.closest('.nav-btn')
        || document.querySelector(`.nav-btn[data-section="${section}"]`);
      if(btn) btn.classList.add('active');
    }

    // Carga inicial: respeta la URL actual (/dashboard/home o /dashboard/{section})
    document.addEventListener('DOMContentLoaded', () => {
      const match = location.pathname.match(/^\/dashboard(?:\/([^\/?#]+))?/);
      const initialSection = (match && match[1]) ? match[1] : 'home';
      markActive(initialSection);
      if(initialSection === 'home'){
        fetch('/dashboard/data')
          .then(r => r.json())
          .then(data => {
            renderSection('home', data);
            const { labels, data: series } = buildChartSeries(data.citasPorDia);
            updateDashboardMetrics(data);
            renderChart(labels, series);
            renderAppointments(data.citas);
            setTodayTexts();
            history.replaceState({ section:'home' }, '', location.pathname);
          })
          .catch(err => console.error('Error al obtener los datos del dashboard:', err));
      } else {
        renderSection(initialSection);
        history.replaceState({ section: initialSection }, '', location.pathname);
      }
    });

  </script>



  <script>

function renderSection(section, data) {
    const mainContent = document.getElementById('mainContent');
    mainContent.innerHTML = '';
  if(section == 'home'){
        mainContent.innerHTML = `
          
      <!-- Main -->
        <!-- Welcome -->
        <div class="mb-3">
          <h1 class="mb-1">¡Bienvenida, {{ $usuario->nombre }}! 👋</h1>
          <p class="text-body-secondary small" id="todayText">Aquí está el resumen de hoy</p>
        </div>

        <!-- Stats -->
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
                <div class="icon-bubble bg-opacity-25 bg-success-subtle text-success"><i class="bi bi-check-circle"></i></div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card card-soft p-4">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-body-secondary small mb-1">Mascotas (Vets)</div>
                  <div class="h4 mb-1" id="mascotasRegistradas">0</div>
                  <div class="small text-success" id="porcentajeMascotasRegistradas">+0%</div>
                </div>
                <div class="icon-bubble bg-opacity-25 bg-info-subtle text-info"><i class="bi bi-heart"></i></div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card card-soft p-4">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-body-secondary small mb-1">Clientes Nuevos (Mtz)</div>
                  <div class="h4 mb-1" id="clientesNuevos">0</div>
                  <div class="small text-success" id="porcentajeClientesNuevos">+0%</div>
                </div>
                <div class="icon-bubble bg-opacity-25 bg-warning-subtle text-warning"><i class="bi bi-person-plus"></i></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart + Activity -->
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
              <div class="vstack gap-3">
                <div class="d-flex gap-3 align-items-start">
                  <div class="icon-bubble bg-opacity-25 bg-primary-subtle text-primary"><i class="bi bi-calendar3"></i></div>
                  <div>
                    <div>Cita completada para Max - Vacunación</div>
                    <div class="small text-body-secondary">Hace 30 min</div>
                  </div>
                </div>
                <div class="d-flex gap-3 align-items-start">
                  <div class="icon-bubble bg-opacity-25 bg-info-subtle text-info"><i class="bi bi-syringe"></i></div>
                  <div>
                    <div>Nuevo cliente registrado: Ana López</div>
                    <div class="small text-body-secondary">Hace 1 hora</div>
                  </div>
                </div>
                <div class="d-flex gap-3 align-items-start">
                  <div class="icon-bubble bg-opacity-25 bg-danger-subtle text-danger"><i class="bi bi-credit-card"></i></div>
                  <div>
                    <div>Pago recibido: $150 - Carlos García</div>
                    <div class="small text-body-secondary">Hace 2 horas</div>
                  </div>
                </div>
                <div class="d-flex gap-3 align-items-start">
                  <div class="icon-bubble bg-opacity-25 bg-warning-subtle text-warning"><i class="bi bi-capsule"></i></div>
                  <div>
                    <div>Nueva mascota registrada: Coco (Canario)</div>
                    <div class="small text-body-secondary">Hace 3 horas</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
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

        `
      }
      // Deja el resto de secciones preparadas para completar luego
      else if(section == 'clientes'){
        // TODO: render clientes
      }
      else if(section == 'mascotas'){
        // TODO: render mascotas
      }
      else if(section == 'citas'){
        // TODO: render citas
      }
      else if(section == 'trabajadores'){
        // TODO: render trabajadores
      }
      else if(section == 'reportes'){
        // TODO: render reportes
      }
      else if(section == 'configuracion'){
        // TODO: render configuracion
      }
    }


    // Manejo de clicks en botones de navegación
    document.querySelectorAll('.nav-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const section = btn.dataset.section || btn.querySelector('[data-section]')?.dataset.section;
        if(!section) return;
        // Marcar activo
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if(section === 'home'){
          fetch('/dashboard/data')
            .then(res => res.json())
            .then(data => {
              renderSection('home', data);
              const { labels, data: series } = buildChartSeries(data.citasPorDia);
              updateDashboardMetrics(data);
              renderChart(labels, series);
              renderAppointments(data.citas);
              setTodayTexts();
              history.pushState({section:'home'}, '', '/dashboard/home');
            });
          return;
        }

        // Para otras secciones por ahora solo renderizamos estructura vacía sin fetch
        renderSection(section);
        history.pushState({section}, '', `/dashboard/${section}`);
      });
    });

// Manejar botón "atrás"/"adelante" del navegador
window.addEventListener('popstate', (event) => {
  const section = event.state?.section || (location.pathname.split('/')[2] || 'home');
  if(section === 'home'){
    fetch('/dashboard/data')
      .then(res => res.json())
      .then(data => {
        renderSection('home', data);
        const { labels, data: series } = buildChartSeries(data.citasPorDia);
        updateDashboardMetrics(data);
        renderChart(labels, series);
        renderAppointments(data.citas);
        setTodayTexts();
      });
  } else {
    renderSection(section);
  }
});

  </script>



@endpush
