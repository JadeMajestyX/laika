@extends('layouts.app_admin')

@section('title', 'VetCare - Panel')

@section('content')
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
@endsection

@push('scripts')
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>


  <script>
    //obtener la información del dashboard desde el servidor (citas de hoy, citas completadas, mascotas, clientes nuevos)

fetch('/dashboard/data')
  .then(response => response.json())
  .then(data => {
    //citas
    
    const citasHoy = document.getElementById('citasHoy');
    const porcentajeCitasElem = document.getElementById('porcentajeCitas');
      const citas = data.citasHoy;
      citasHoy.textContent = citas;
      const cambio = data.comparacionporcentaje['citasHoy'];
      porcentajeCitasElem.textContent = (cambio >= 0 ? '+' : '') + cambio + '%';
      porcentajeCitasElem.className = 'small ' + (cambio >= 0 ? 'text-success' : 'text-danger');

      //citas completadas
      const citasCompletadas = document.getElementById('citasCompletadas');
      const porcentajeCitasCompletadasElem = document.getElementById('porcentajeCitasCompletadas');
      const citasComp = data.citasCompletadas;
      citasCompletadas.textContent = citasComp;
      const cambioComp = data.comparacionporcentaje['citasCompletadas'];
      porcentajeCitasCompletadasElem.textContent = (cambioComp >= 0 ? '+' : '') + cambioComp + '%';
      porcentajeCitasCompletadasElem.className = 'small ' + (cambioComp >= 0 ? 'text-success' : 'text-danger');

      //mascotas registradas
      const mascotasRegistradas = document.getElementById('mascotasRegistradas');
      const porcentajeMascotasRegistradasElem = document.getElementById('porcentajeMascotasRegistradas');
      const mascotas = data.mascotasRegistradas;
      mascotasRegistradas.textContent = mascotas;
      const cambioMascotas = data.comparacionporcentaje['mascotasRegistradas'];
      porcentajeMascotasRegistradasElem.textContent = (cambioMascotas >= 0 ? '+' : '') + cambioMascotas + '%';
      porcentajeMascotasRegistradasElem.className = 'small ' + (cambioMascotas >= 0 ? 'text-success' : 'text-danger');

      //clientes nuevos
      const clientesNuevos = document.getElementById('clientesNuevos');
      const porcentajeClientesNuevosElem = document.getElementById('porcentajeClientesNuevos');
      const clientes = data.clientesNuevos;
      clientesNuevos.textContent = clientes;
      const cambioClientes = data.comparacionporcentaje['clientesNuevos'];
      porcentajeClientesNuevosElem.textContent = (cambioClientes >= 0 ? '+' : '') + cambioClientes + '%';
      porcentajeClientesNuevosElem.className = 'small ' + (cambioClientes >= 0 ? 'text-success' : 'text-danger');

  }).catch(error => {
    console.error('Error al obtener los datos del dashboard:', error);
  });

</script>

  <script>
    // Datos de ejemplo (equivalentes a los del proyecto React)
    const chartData = [12, 15, 8, 18, 14, 6, 3];
    const chartLabels = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

    const appointments = [
      { time: '09:00', pet: 'Max', owner: 'Carlos García', breed: 'Golden Retriever', reason: 'Vacunación anual', clinic: 'Clínica Principal', status: 'Confirmado', petType: 'dog' },
      { time: '10:30', pet: 'Luna', owner: 'María Rodríguez', breed: 'Siamés', reason: 'Control general', clinic: 'Clínica Principal', status: 'Confirmado', petType: 'cat' },
      { time: '11:00', pet: 'Rocky', owner: 'Juan Pérez', breed: 'Bulldog Francés', reason: 'Problemas respiratorios', clinic: 'Clínica Sur', status: 'En Progreso', petType: 'dog' },
      { time: '14:00', pet: 'Mia', owner: 'Ana López', breed: 'Persa', reason: 'Esterilización', clinic: 'Clínica Principal', status: 'Programado', petType: 'cat' },
      { time: '15:30', pet: 'Suly', owner: 'Pedro Martínez', breed: 'Beagle', reason: 'Revisión dental', clinic: 'Clínica Norte', status: 'Programado', petType: 'dog' },
      { time: '16:00', pet: 'Coco', owner: 'Laura Sánchez', breed: 'Canario', reason: 'Control rutinario', clinic: 'Clínica Principal', status: 'Programado', petType: 'dog' },
    ];

    // Formatear fecha de hoy en español
    function setTodayTexts(){
      const date = new Date();
      const fmtDate = date.toLocaleDateString('es-ES',{ day:'2-digit', month:'2-digit', year:'numeric' });
      const fmtLong = new Intl.DateTimeFormat('es-ES', { weekday:'long', day:'numeric', month:'long', year:'numeric' }).format(date);
      document.getElementById('todayDate').textContent = fmtDate;
      document.getElementById('todayText').textContent = `Aquí está el resumen de hoy, ${fmtLong}`;
    }

    // Render tabla
    function renderAppointments(){
      const tbody = document.getElementById('appointmentsBody');
      tbody.innerHTML = '';
      for(const apt of appointments){
  const badge = (status => {
          switch(status){
            case 'Confirmado': return '<span class="badge text-bg-success-subtle border-0">Confirmado</span>';
            case 'En Progreso': return '<span class="badge text-bg-warning-subtle border-0">En Progreso</span>';
            case 'Programado': return '<span class="badge text-bg-primary-subtle border-0">Programado</span>';
            default: return '<span class="badge text-bg-secondary border-0">-</span>';
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
      }
    }

    // Chart.js
    let chartInstance = null;
    function renderChart(){
      const ctx = document.getElementById('appointmentsChart');
      if(!ctx) return;
      // Destroy previous chart if exists (prevents multiple instances/resizes)
      if(chartInstance){ chartInstance.destroy(); }
      const gridColor = getComputedStyle(document.querySelector('[data-bs-theme]'))
        .getPropertyValue('--bs-border-color').trim() || '#e9ecef';
      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: chartLabels,
          datasets: [{
            label: 'Citas',
            data: chartData,
            backgroundColor: '#3A7CA5',
            borderRadius: 8,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false, // use the .chart-container height
          scales: {
            x: { grid: { display: false }, ticks: { color: getTextColor() } },
            y: { grid: { color: gridColor }, ticks: { color: getTextColor() } }
          },
          plugins: { legend: { display:false } }
        }
      });
    }

    // Init
    document.addEventListener('DOMContentLoaded', () =>{
      setTodayTexts();
      renderAppointments();
      renderChart();
    });
  </script>
@endpush
