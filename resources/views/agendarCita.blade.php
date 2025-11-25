{{-- resources/views/agendar-cita.blade.php --}}
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agendar cita - Laika</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand: #3A7CA5;
      --brand-dark: #2f6485;
      --muted: #6b7a82;
      --card-bg: #ffffff;
      --surface: #f8f9fa;
    }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background-color: var(--surface);
      color: #333;
      padding-top: 80px;
    }
    .card { border-radius: 1rem; }

    /* STEPper + conectores azules */
    .stepper {
      display:flex;
      gap:.75rem;
      justify-content:center;
      margin-bottom:1.25rem;
      align-items:center;
      position:relative;
      /* espacio lateral para que los conectores no se corten */
      padding: 0 12px;
    }
    .step {
      width:42px;
      height:42px;
      border-radius:50%;
      background:#e9f2f7;
      color:var(--brand);
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      box-shadow:0 2px 4px rgba(0,0,0,0.04);
      position:relative;
      z-index:2; /* encima de la barra */
    }

    /* barra / conector entre números (excepto el último) */
    .step:not(:last-child)::after{
      content: "";
      position: absolute;
      top: 50%;
      right: -27px;              /* ajusta para centrar la barra entre círculos */
      transform: translateY(-50%);
      width: 54px;               /* ancho aproximado entre centros (42 + gap≈12) */
      height: 6px;
      border-radius: 6px;
      background: rgba(58,124,165,0.15); /* estado inactivo por defecto */
      z-index:1;
    }

    /* cuando el paso es activo, que la barra desde ese paso hacia la siguiente sea azul/gradiente */
    .step.active::after{
      background: linear-gradient(90deg, var(--brand), var(--brand-dark));
      height: 6px;
    }

    /* Si queremos que los pasos anteriores al activo muestren la barra activa también:
       seleccionamos todos los .step anteriores al active con JS o con una clase .completed.
       En este ejemplo no usamos JS adicional; si deseas que pasos previos muestren la barra
       activa, en la función de navegación añade/remueve la clase .completed en los .step. */
    .step.completed::after{
      background: linear-gradient(90deg, var(--brand), var(--brand-dark));
      height: 6px;
    }

    .step.active { background:linear-gradient(180deg,var(--brand),var(--brand-dark)); color:#fff; }

    .step-labels { display:flex; gap:1.25rem; justify-content:center; margin-bottom:1.5rem; font-size:.9rem; color:var(--muted); }

    /* calendario / botones horarios (sin cambios importantes) */
    .calendar {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: .5rem;
      text-align: center;
      margin-top: 1rem;
    }
    .calendar .day {
      padding: .6rem 0;
      border-radius: 50%;
      cursor: pointer;
      color: #333;
    }
    .calendar .day:hover { background-color: #e9f2f7; }
    .calendar .day.active {
      background-color: var(--brand);
      color: #fff;
      font-weight: 600;
    }
    .hour-btn {
      border: 1px solid var(--brand);
      color: var(--brand);
      border-radius: 8px;
      background: transparent;
      padding: .4rem 1.25rem;
      margin: .3rem;
      transition: all .2s ease;
    }
    .hour-btn:hover, .hour-btn.active {
      background-color: var(--brand);
      color: #fff;
    }

    /* --- Estilos del resumen tipo tarjeta (similar a la imagen) --- */
    .summary-card {
      background: var(--card-bg);
      border-radius: 14px;
      box-shadow: 0 6px 22px rgba(31,50,68,0.06);
      padding: 16px;
      max-width: 560px;
      margin: 0 auto;
      border: 1px solid rgba(58,124,165,0.06);
    }
    .summary-list { list-style: none; padding: 0; margin: 0; }
    .summary-item {
      display:flex;
      justify-content:space-between;
      gap:.75rem;
      align-items:flex-start;
      padding: 14px 6px;
      border-bottom: 1px solid rgba(20,40,60,0.04);
    }
    .summary-item:last-child { border-bottom: none; }
    .summary-left { display:flex; gap:.75rem; align-items:flex-start; min-width:0; }
    .summary-icon {
      width:28px; height:28px; border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      background: linear-gradient(180deg, rgba(58,124,165,0.12), rgba(47,100,133,0.08));
      color: var(--brand);
      flex:0 0 36px;
      font-size: 16px;
      margin-top:2px;
    }
    .summary-text { min-width:0; }
    .summary-title { font-weight:600; color:#24323b; font-size:.98rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .summary-sub { display:block; color:var(--muted); font-size:.85rem; margin-top:4px; white-space:normal; }
    .modify-link {
      color: var(--brand);
      text-decoration:none;
      font-weight:700;
      font-size:.95rem;
      margin-left: 8px;
      white-space:nowrap;
    }
    .modify-link:hover { text-decoration: underline; color: var(--brand-dark); }

    @media (min-width:992px){ .form-wrapper{max-width:920px; margin:0 auto;} }

    /* responsive: reduce gap y conector en pantallas pequeñas */
    @media (max-width: 576px) {
      :root { --step-gap: 1.25rem; --step-size: 36px; --connector-height: 5px; }
      .step-labels > div { font-size: .8rem; width: var(--step-size); }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm" style="background: linear-gradient(90deg,var(--brand),var(--brand-dark));">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('welcome') }}">
        <i class="bi bi-heart-pulse-fill me-2"></i> Laika
      </a>
    </div>
  </nav>

  <main class="container form-wrapper">
    <div class="card shadow-sm p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="mb-1">Agendar cita</h2>
          <small class="text-muted">Sigue los pasos para programar la cita de tu mascota</small>
        </div>
        <a href="{{ route('welcome') }}" class="btn btn-link small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver</a>
      </div>

      @if(session('recordatorio_result'))
        @php($r = session('recordatorio_result'))
        <div class="alert alert-success mt-3">
          Recordatorio enviado: usuarios {{ $r['usuarios'] }}, éxitos {{ $r['exitosos'] }}, fallos {{ $r['fallidos'] }}.
        </div>
      @endif

      @if(\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->rol === 'A')
        <div class="mt-3 p-3 border rounded bg-light">
          <form method="POST" action="{{ url('/citas/recordatorio-hoy') }}" class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
            @csrf
            <div class="flex-grow-1">
              <strong>Recordatorio diario:</strong> Enviar notificación a todos los usuarios con citas hoy.
            </div>
            <button class="btn btn-primary" type="submit" id="btnRecordatorioHoy">
              <i class="bi bi-bell"></i> Enviar recordatorio hoy
            </button>
          </form>
          <small class="text-muted d-block mt-2">Se notificará una vez por ejecución. Tokens inválidos se depuran automáticamente.</small>
        </div>
      @endif

      <!-- Stepper -->
      <div class="mt-4">
        <div class="stepper" id="stepper">
          <div class="step active" data-step="1">1</div>
          <div class="step" data-step="2">2</div>
          <div class="step" data-step="3">3</div>
          <div class="step" data-step="4">4</div>
          <div class="step" data-step="5">5</div>
        </div>
        <div class="step-labels">
          <div>Clínica</div>
          <div>Servicio</div>
          <div>Mascota</div>
          <div>Fecha y profesional</div>
          <div>Resumen</div>
        </div>
      </div>

      <form id="appointmentForm" method="POST">
        @csrf

        <!-- STEP 1: Clínica -->
        <div class="step-content" data-step="1">
          <div class="row g-3">
            <div class="col-12">
              <label for="clinic" class="form-label">Selecciona la clínica <span class="required">*</span></label>
              <select id="clinic" name="clinic" class="form-select" required>
                <option value="">Selecciona una clínica</option>
                <option value="Vetalia - Condesa">Vetalia - Condesa</option>
                <option value="Clínica Laika Norte">Clínica Laika Norte</option>
                <option value="Clínica Laika Sur">Clínica Laika Sur</option>
              </select>
            </div>
          </div>
        </div>

        <!-- STEP 2: Servicio -->
        <div class="step-content d-none" data-step="2">
          <div class="col-12 mb-3">
            <label for="service" class="form-label">Servicio requerido <span class="required">*</span></label>
            <select id="service" name="service" class="form-select" required>
              <option value="">Selecciona un servicio</option>
              <option value="Corte de pelo y baño">Corte de pelo y baño</option>
              <option value="Baño">Baño</option>
              <option value="Visita médica">Visita médica</option>
            </select>
          </div>

          <div id="medical-options" class="d-none">
            <label for="medical_reason" class="form-label">Motivo de la cita médica</label>
            <select id="medical_reason" name="medical_reason" class="form-select">
              <option value="">Selecciona una opción</option>
              <option value="Consulta general">Consulta general</option>
              <option value="Vacunación">Vacunación</option>
            </select>
          </div>
        </div>

        <!-- STEP 3: Mascota -->
        <div class="step-content d-none" data-step="3">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="pet_name" class="form-label">Nombre de la mascota <span class="required">*</span></label>
              <input type="text" id="pet_name" name="pet_name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="species" class="form-label">Especie <span class="required">*</span></label>
              <select id="species" name="species" class="form-select" required>
                <option value="">Selecciona una especie</option>
                <option value="Perro">Perro</option>
                <option value="Gato">Gato</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="breed" class="form-label">Raza</label>
              <input type="text" id="breed" name="breed" class="form-control">
            </div>
            <div class="col-md-6">
              <label for="age" class="form-label">Edad aproximada</label>
              <input type="text" id="age" name="age" class="form-control">
            </div>
          </div>
        </div>

        <!-- STEP 4: Fecha y profesional -->
        <div class="step-content d-none" data-step="4">
          <label class="form-label">Selecciona un profesional</label>
          <select id="professional" name="professional" class="form-select mb-3" required>
            <option value="">Cualquier profesional</option>
            <option value="Dra. Gómez">Dra. Gómez</option>
            <option value="Dr. Martínez">Dr. Martínez</option>
            <option value="Dra. Hernández">Dra. Hernández</option>
          </select>

          <div class="text-center mb-2 fw-semibold">Noviembre</div>
          <div class="calendar" id="calendar"></div>

          <div id="timeSlots" class="text-center mt-3"></div>
        </div>

        <!-- STEP 5: Resumen (nuevo estilo tipo tarjeta) -->
        <div class="step-content d-none" data-step="5">
          <h5 class="mb-3">Revisar datos y confirmar cita</h5>

          <div class="summary-card">
            <ul class="summary-list">
              <li class="summary-item">
                <div class="summary-left">
                  <div class="summary-icon"><i class="bi bi-geo-alt-fill"></i></div>
                  <div class="summary-text">
                    <div class="summary-title" id="sum_clinic">—</div>
                    <div class="summary-sub" id="sum_clinic_address">—</div>
                  </div>
                </div>
                <div><a href="#" class="modify-link" data-step="1">Modificar</a></div>
              </li>

              <li class="summary-item">
                <div class="summary-left">
                  <div class="summary-icon"><i class="bi bi-heart-fill"></i></div>
                  <div class="summary-text">
                    <div class="summary-title" id="sum_service">—</div>
                    <div class="summary-sub" id="sum_service_sub"></div>
                  </div>
                </div>
                <div><a href="#" class="modify-link" data-step="2">Modificar</a></div>
              </li>

              <li class="summary-item">
                <div class="summary-left">
                  <div class="summary-icon"><i class="bi bi-person-fill"></i></div>
                  <div class="summary-text">
                    <div class="summary-title" id="sum_owner">—</div>
                  </div>
                </div>
                <div><a href="#" class="modify-link" data-step="3">Modificar</a></div>
              </li>

              <li class="summary-item">
                <div class="summary-left">
                  <div class="summary-icon"><i class="bi bi-paw-fill"></i></div>
                  <div class="summary-text">
                    <div class="summary-title" id="sum_pet">—</div>
                    <div class="summary-sub" id="sum_pet_sub">—</div>
                  </div>
                </div>
                <div><a href="#" class="modify-link" data-step="3">Modificar</a></div>
              </li>

              <li class="summary-item">
                <div class="summary-left">
                  <div class="summary-icon"><i class="bi bi-person-badge-fill"></i></div>
                  <div class="summary-text">
                    <div class="summary-title" id="sum_professional">Cualquier profesional</div>
                  </div>
                </div>
                <div><a href="#" class="modify-link" data-step="4">Modificar</a></div>
              </li>

              <li class="summary-item">
                <div class="summary-left">
                  <div class="summary-icon"><i class="bi bi-calendar-fill"></i></div>
                  <div class="summary-text">
                    <div class="summary-title" id="sum_date">—</div>
                    <div class="summary-sub" id="sum_time">—</div>
                  </div>
                </div>
                <div><a href="#" class="modify-link" data-step="4">Modificar</a></div>
              </li>
            </ul>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <button type="button" id="prevBtn" class="btn btn-outline-secondary">
              <i class="bi bi-chevron-left"></i> Anterior
            </button>
            <div>
              <button type="submit" id="submitBtn" class="btn btn-primary">Confirmar cita</button>
            </div>
          </div>
        </div>

        <!-- Botones generales (para pasos 1-4, se ocultan en step 5) -->
        <div class="d-flex justify-content-between mt-4" id="navButtons">
          <button type="button" id="prevBtnGeneral" class="btn btn-outline-secondary" style="visibility:hidden;">
            <i class="bi bi-chevron-left"></i> Anterior
          </button>
          <div>
            <button type="button" id="nextBtn" class="btn btn-primary">Siguiente <i class="bi bi-chevron-right"></i></button>
          </div>
        </div>

      </form>
    </div>
  </main>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      const steps = document.querySelectorAll('.step');
      const contents = document.querySelectorAll('.step-content');
      const prevBtn = document.getElementById('prevBtnGeneral');
      const nextBtn = document.getElementById('nextBtn');
      const submitBtn = document.getElementById('submitBtn');
      let current = 1;

      const calendarEl = document.getElementById('calendar');
      const timeSlotsEl = document.getElementById('timeSlots');

      // elementos resumen
      const sum_clinic = document.getElementById('sum_clinic');
      const sum_clinic_address = document.getElementById('sum_clinic_address');
      const sum_service = document.getElementById('sum_service');
      const sum_service_sub = document.getElementById('sum_service_sub');
      const sum_owner = document.getElementById('sum_owner');
      const sum_pet = document.getElementById('sum_pet');
      const sum_pet_sub = document.getElementById('sum_pet_sub');
      const sum_professional = document.getElementById('sum_professional');
      const sum_date = document.getElementById('sum_date');
      const sum_time = document.getElementById('sum_time');

      // inputs
      const clinicEl = document.getElementById('clinic');
      const serviceEl = document.getElementById('service');
      const petNameEl = document.getElementById('pet_name');
      const speciesEl = document.getElementById('species');
      const breedEl = document.getElementById('breed');
      const professionalEl = document.getElementById('professional');

      let selectedDate = null, selectedTime = null;

      // direcciones demo para cada clínica (ajústalas a tu BD real)
      const clinicAddresses = {
        "Vetalia - Condesa": "Av Nuevo León 155, Hipódromo, Cuauhtémoc, 06100 Ciudad de México, CDMX, México",
        "Clínica Laika Norte": "Av Norte 200, Col. Norte, CP 12345",
        "Clínica Laika Sur": "Av Sur 50, Col. Sur, CP 54321"
      };

      // Generar calendario actual (mes completo)
      const now = new Date();
      const year = now.getFullYear();
      const month = now.getMonth();
      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const dayNames = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];

      // Mostrar nombres de días
      dayNames.forEach(d => {
        const label = document.createElement('div');
        label.innerHTML = `<strong>${d}</strong>`;
        label.classList.add('small', 'text-muted');
        calendarEl.appendChild(label);
      });

      // ajustar primer día (asumiendo Lunes como primer columna)
      const offset = (firstDay === 0) ? 6 : firstDay - 1;
      for (let i = 0; i < offset; i++) {
        const empty = document.createElement('div');
        calendarEl.appendChild(empty);
      }

      for (let d = 1; d <= daysInMonth; d++) {
        const day = document.createElement('div');
        day.classList.add('day');
        day.textContent = d;
        day.onclick = (e) => selectDate(d, e);
        calendarEl.appendChild(day);
      }

      function selectDate(day, e) {
        selectedDate = new Date(year, month, day);
        document.querySelectorAll('.calendar .day').forEach(d=>d.classList.remove('active'));
        e.currentTarget.classList.add('active');
        showTimeSlots();
      }

      function showTimeSlots() {
        const hours = ["09:00","09:30","10:00","10:30","11:00","11:30","12:00","12:30","13:00","13:30","15:00","15:30","16:00","16:30","17:00","17:30"];
        timeSlotsEl.innerHTML = hours.map(h=>`<button type="button" class="hour-btn">${h}</button>`).join('');
        document.querySelectorAll('.hour-btn').forEach(btn=>{
          btn.onclick=()=>{
            document.querySelectorAll('.hour-btn').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            selectedTime = btn.textContent;
            // pasar automáticamente al resumen
            goToStep(5);
            populateSummary();
          };
        });
      }

      function populateSummary(){
        // Clínica y dirección
        const clinic = clinicEl.value || '—';
        sum_clinic.textContent = clinic;
        sum_clinic_address.textContent = clinicAddresses[clinic] || '';

        // Servicio
        const service = serviceEl.value || '—';
        sum_service.textContent = service;
        // si hay motivo médico
        const medReason = document.getElementById('medical_reason')?.value || '';
        sum_service_sub.textContent = medReason ? medReason : '';

        // Dueño/solicitante
        sum_owner.textContent = "{{ Auth::user()->name ?? 'Jose Angel' }}";

        // Mascota (nombre + especie, raza)
        const petName = petNameEl.value || '—';
        const species = speciesEl.value ? `, ${speciesEl.value}` : '';
        sum_pet.textContent = petName + (species ? species : '');
        sum_pet_sub.textContent = breedEl.value ? breedEl.value : '';

        // Profesional
        const prof = professionalEl.value || 'Cualquier profesional';
        sum_professional.textContent = prof;

        // Fecha y hora
        if (selectedDate) {
          const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
          sum_date.textContent = selectedDate.toLocaleDateString('es-ES', opts);
        } else {
          sum_date.textContent = '—';
        }
        sum_time.textContent = selectedTime || '—';
      }

      function showStep(step){
        current = step;
        steps.forEach(s=>{
          const isActive = Number(s.dataset.step)===step;
          s.classList.toggle('active', isActive);
          const stepIndex = Number(s.dataset.step);
          s.classList.toggle('completed', stepIndex < step);
        });
        contents.forEach(c=>c.classList.toggle('d-none',Number(c.dataset.step)!==step));

        document.getElementById('navButtons').style.display = (step===5)?'none':'flex';
        prevBtn.style.visibility = step===1?'hidden':'visible';
      }

      function goToStep(step){
        if (step === 5) populateSummary();
        showStep(step);
      }

      nextBtn.onclick = () => {
        if (current < contents.length) {
          goToStep(current + 1);
          if (current+1 === 5) populateSummary();
        }
      };
      prevBtn.onclick = () => {
        if (current > 1) goToStep(current - 1);
      };

      document.querySelectorAll('.modify-link').forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const step = Number(link.getAttribute('data-step')) || 1;
          goToStep(step);
        });
      });

      [clinicEl, serviceEl, petNameEl, speciesEl, breedEl, professionalEl].forEach(inp=>{
        if(!inp) return;
        inp.addEventListener('change', () => {
          if (current === 5) populateSummary();
        });
      });

      showStep(current);

      document.getElementById('appointmentForm').addEventListener('submit', function(e){
        // enviar normalmente
      });
    })();
  </script>
</body>
</html>
