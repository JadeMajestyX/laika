{{-- resources/views/agendar-cita.blade.php --}}
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agendar cita - Laika</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand: #3A7CA5;
      --brand-hover: #2c5f7f;
      --brand-light: #eef6fa;
      --text-main: #2c3e50;
      --text-muted: #6c757d;
      --border-color: #e2e8f0;
      --radius-md: 12px;
      --radius-lg: 16px;
      --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
      --shadow-hover: 0 8px 16px rgba(58,124,165,0.1);
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f4f7f9;
      color: var(--text-main);
      padding-top: 80px;
      padding-bottom: 40px;
    }

    /* --- Utilities & Form Styling --- */
    .form-wrapper { max-width: 850px; margin: 0 auto; }
    
    .card-main {
      border: none;
      border-radius: var(--radius-lg);
      background: #fff;
      box-shadow: 0 4px 24px rgba(0,0,0,0.05);
      overflow: hidden;
    }

    .form-control, .form-select {
      padding: 0.75rem 1rem;
      border-radius: var(--radius-md);
      border: 1px solid var(--border-color);
      font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--brand);
      box-shadow: 0 0 0 4px rgba(58, 124, 165, 0.15);
    }
    .form-label { font-weight: 500; font-size: 0.9rem; margin-bottom: 0.5rem; color: #4a5568; }
    .required { color: #dc3545; }

    /* --- Stepper Moderno --- */
    .stepper-container {
      padding: 1.5rem 0;
      margin-bottom: 2rem;
      position: relative;
    }
    .stepper {
      display: flex;
      justify-content: space-between;
      position: relative;
      margin-bottom: 10px;
    }
    /* Línea de fondo */
    .stepper::before {
      content: '';
      position: absolute;
      top: 50%; left: 0; right: 0;
      transform: translateY(-50%);
      height: 4px;
      background: #e9ecef;
      z-index: 1;
      border-radius: 4px;
    }
    /* Línea de progreso (se llena con JS si quieres, o estático por paso) */
    .progress-line {
      position: absolute;
      top: 50%; left: 0;
      transform: translateY(-50%);
      height: 4px;
      background: var(--brand);
      z-index: 1;
      transition: width 0.4s ease;
      border-radius: 4px;
    }
    
    .step-item {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
      cursor: default;
    }
    .step-circle {
      width: 40px; height: 40px;
      border-radius: 50%;
      background: #fff;
      border: 2px solid #e9ecef;
      display: flex;
      align-items: center; justify-content: center;
      font-weight: 600; color: var(--text-muted);
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }
    .step-item.active .step-circle {
      border-color: var(--brand);
      background: var(--brand);
      color: #fff;
      box-shadow: 0 0 0 4px rgba(58,124,165,0.2);
    }
    .step-item.completed .step-circle {
      border-color: var(--brand);
      background: #fff;
      color: var(--brand);
    }
    .step-label {
      margin-top: 8px;
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .step-item.active .step-label { color: var(--brand); }

    /* --- Radio Cards (Selección Visual) --- */
    /* Ocultamos el radio button real */
    .radio-card-input { display: none; }
    
    .radio-card {
      display: block;
      cursor: pointer;
      height: 100%;
    }
    .radio-card-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      border: 2px solid var(--border-color);
      border-radius: var(--radius-md);
      background: #fff;
      transition: all 0.2s ease;
      height: 100%;
      text-align: center;
    }
    .radio-card:hover .radio-card-content {
      border-color: #cbd5e0;
      background: #f8fafc;
    }
    /* Estado seleccionado */
    .radio-card-input:checked + .radio-card-content {
      border-color: var(--brand);
      background: var(--brand-light);
      color: var(--brand);
      box-shadow: 0 4px 12px rgba(58,124,165,0.15);
    }
    .radio-icon {
      font-size: 2rem;
      margin-bottom: 0.75rem;
      color: #a0aec0;
      transition: color 0.2s;
    }
    .radio-card-input:checked + .radio-card-content .radio-icon {
      color: var(--brand);
    }
    .radio-title { font-weight: 600; font-size: 1rem; }

    /* --- Calendario Mejorado --- */
    .calendar-container {
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 1.5rem;
    }
    .calendar-header {
      font-weight: 700;
      text-align: center;
      margin-bottom: 1rem;
      color: var(--brand);
      text-transform: capitalize;
    }
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 8px;
      text-align: center;
    }
    .day-name { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-bottom: 8px;}
    .day-number {
      height: 38px;
      display: flex;
      align-items: center; justify-content: center;
      border-radius: 50%;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.2s;
    }
    .day-number:hover:not(.active) { background: #f1f5f9; color: var(--brand); }
    .day-number.active { background: var(--brand); color: #fff; font-weight: 600; box-shadow: 0 4px 10px rgba(58,124,165,0.3); }
    
    .time-slots {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
      gap: 10px;
      margin-top: 1.5rem;
    }
    .time-btn {
      border: 1px solid var(--border-color);
      background: #fff;
      color: var(--text-main);
      padding: 0.5rem;
      border-radius: 8px;
      font-size: 0.9rem;
      transition: all 0.2s;
    }
    .time-btn:hover { border-color: var(--brand); color: var(--brand); }
    .time-btn.active { background: var(--brand); color: #fff; border-color: var(--brand); }

    /* --- Animation --- */
    .step-content { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* --- Summary Card --- */
    .summary-box {
      background: #f8fafc;
      border-radius: var(--radius-md);
      padding: 1.5rem;
      border: 1px solid var(--border-color);
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #edf2f7;
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-label { display: flex; align-items: center; gap: 10px; font-weight: 500; color: #64748b; }
    .summary-label i { color: var(--brand); font-size: 1.1rem; }
    .summary-value { font-weight: 600; color: #2d3748; text-align: right; }
    .btn-edit { font-size: 0.8rem; color: var(--brand); text-decoration: none; margin-left: 10px; font-weight: 600;}
    
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg fixed-top shadow-sm" style="background: #3A7CA5; height: 70px;">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center text-dark" href="{{ route('welcome') }}">
        <i class="bi bi-heart-pulse-fill text-white me-2" style="font-size: 1.5rem;"></i> 
        <span style="letter-spacing: -0.5px;" class="text-white">Laika</span>
      </a>
    </div>
  </nav>

  <main class="container form-wrapper">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('welcome') }}" class="btn btn-light rounded-circle me-3 shadow-sm" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h3 class="fw-bold mb-0">Agendar nueva cita</h3>
            <p class="text-muted small mb-0">Completa los pasos para reservar</p>
        </div>
    </div>

    <div class="card card-main p-4 p-md-5">
      
      <div class="stepper-container">
        <div class="stepper">
            <div class="progress-line" id="progressLine" style="width: 0%;"></div>
            
            <div class="step-item active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label d-none d-sm-block">Clínica</div>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label d-none d-sm-block">Servicio</div>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label d-none d-sm-block">Mascota</div>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label d-none d-sm-block">Fecha</div>
            </div>
            <div class="step-item" data-step="5">
                <div class="step-circle">5</div>
                <div class="step-label d-none d-sm-block">Fin</div>
            </div>
        </div>
      </div>

      <form id="appointmentForm" method="POST">
        @csrf

        <div class="step-content" data-step="1">
          <h5 class="fw-bold mb-4">¿En qué clínica te gustaría ser atendido?</h5>
          <div class="row g-3">
            <div class="col-12">
                <div class="form-floating">
                    <select id="clinic" name="clinic" class="form-select" required style="height: 65px;">
                        <option value="">Seleccionar ubicación...</option>
                    </select>
                    <label for="clinic">Ubicación</label>
                </div>
                <div class="mt-3 p-3 bg-light rounded border" id="clinicInfo" style="display:none;">
                    <small class="text-muted d-block fw-bold">Dirección:</small>
                    <span id="clinicAddressDisplay" class="small"></span>
                </div>
            </div>
          </div>
        </div>

        <div class="step-content d-none" data-step="2">
            <h5 class="fw-bold mb-4">¿Qué servicio necesita tu mascota?</h5>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="radio-card">
                        <input type="radio" name="service" value="Corte de pelo y baño" class="radio-card-input" required>
                        <div class="radio-card-content">
                            <i class="bi bi-scissors radio-icon"></i>
                            <div class="radio-title">Estética</div>
                            <small class="text-muted">Corte y baño</small>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="radio-card">
                        <input type="radio" name="service" value="Baño" class="radio-card-input">
                        <div class="radio-card-content">
                            <i class="bi bi-droplet-half radio-icon"></i>
                            <div class="radio-title">Sólo Baño</div>
                            <small class="text-muted">Limpieza profunda</small>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="radio-card">
                        <input type="radio" name="service" value="Visita médica" class="radio-card-input">
                        <div class="radio-card-content">
                            <i class="bi bi-heart-pulse radio-icon"></i>
                            <div class="radio-title">Consulta</div>
                            <small class="text-muted">Revisión médica</small>
                        </div>
                    </label>
                </div>
            </div>

            <div id="medical-options" class="d-none mt-3">
                <label for="medical_reason" class="form-label">Tipo de consulta</label>
                <select id="medical_reason" name="medical_reason" class="form-select">
                    <option value="">Selecciona...</option>
                    <option value="Consulta general">Consulta general</option>
                    <option value="Vacunación">Vacunación</option>
                    <option value="Desparasitación">Desparasitación</option>
                </select>
            </div>
        </div>

        <div class="step-content d-none" data-step="3">
            <h5 class="fw-bold mb-4">Datos del paciente</h5>
            
            <label class="form-label">Especie <span class="required">*</span></label>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <label class="radio-card">
                        <input type="radio" name="species" value="Perro" class="radio-card-input" required>
                        <div class="radio-card-content p-3">
                            <i class="bi bi-emoji-smile radio-icon mb-1"></i> <div class="radio-title fs-6">Perro</div>
                        </div>
                    </label>
                </div>
                <div class="col-6 col-md-3">
                    <label class="radio-card">
                        <input type="radio" name="species" value="Gato" class="radio-card-input">
                        <div class="radio-card-content p-3">
                            <i class="bi bi-emoji-heart-eyes radio-icon mb-1"></i>
                            <div class="radio-title fs-6">Gato</div>
                        </div>
                    </label>
                </div>
                <div class="col-6 col-md-3">
                    <label class="radio-card">
                        <input type="radio" name="species" value="Otro" class="radio-card-input">
                        <div class="radio-card-content p-3">
                            <i class="bi bi-question-circle radio-icon mb-1"></i>
                            <div class="radio-title fs-6">Otro</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-12">
                    <label for="pet_name" class="form-label">Nombre de la mascota <span class="required">*</span></label>
                    <input type="text" id="pet_name" name="pet_name" class="form-control form-control-lg" placeholder="Ej. Firulais" required>
                </div>
                <div class="col-md-6">
                    <label for="breed" class="form-label">Raza</label>
                    <input type="text" id="breed" name="breed" class="form-control" placeholder="Opcional">
                </div>
                <div class="col-md-6">
                    <label for="age" class="form-label">Edad (Años/Meses)</label>
                    <input type="text" id="age" name="age" class="form-control" placeholder="Ej. 2 años">
                </div>
            </div>
        </div>

        <div class="step-content d-none" data-step="4">
            <h5 class="fw-bold mb-4">Elige fecha y hora</h5>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <label class="form-label">Especialista preferido</label>
                    <select id="professional" name="professional" class="form-select mb-3">
                        <option value="">Cualquier profesional disponible</option>
                        <option value="Dra. Gómez">Dra. Gómez</option>
                        <option value="Dr. Martínez">Dr. Martínez</option>
                        <option value="Dra. Hernández">Dra. Hernández</option>
                    </select>

                    <div class="calendar-container">
                        <div class="calendar-header" id="calendarHeader">Mes Año</div>
                        <div class="calendar-grid" id="calendar">
                            </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="alert alert-light border text-center" id="dateFeedback">
                        <i class="bi bi-calendar-check me-2 text-brand"></i> Selecciona un día para ver horarios
                    </div>
                    
                    <div id="timeContainer" class="d-none animate-fade">
                        <p class="small text-muted text-center fw-bold">Horarios disponibles</p>
                        <div class="time-slots" id="timeSlots"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="step-content d-none" data-step="5">
            <div class="text-center mb-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-check-lg text-success fs-2"></i>
                </div>
                <h4 class="fw-bold">Confirma tu cita</h4>
                <p class="text-muted">Verifica que los datos sean correctos antes de finalizar.</p>
            </div>

            <div class="summary-box">
                <div class="summary-row">
                    <div class="summary-label"><i class="bi bi-geo-alt"></i> Clínica</div>
                    <div class="text-end">
                        <div class="summary-value" id="sum_clinic"></div>
                        <small class="d-block text-muted" id="sum_clinic_address" style="font-size:0.75rem; max-width:200px; margin-left:auto;"></small>
                        <a href="#" class="btn-edit" data-step="1">Cambiar</a>
                    </div>
                </div>

                <div class="summary-row">
                    <div class="summary-label"><i class="bi bi-stars"></i> Servicio</div>
                    <div class="text-end">
                        <div class="summary-value" id="sum_service"></div>
                        <small class="d-block text-muted" id="sum_service_sub"></small>
                        <a href="#" class="btn-edit" data-step="2">Cambiar</a>
                    </div>
                </div>

                <div class="summary-row">
                    <div class="summary-label"><i class="bi bi-paw"></i> Mascota</div>
                    <div class="text-end">
                        <div class="summary-value" id="sum_pet"></div>
                        <small class="d-block text-muted" id="sum_pet_sub"></small>
                        <a href="#" class="btn-edit" data-step="3">Cambiar</a>
                    </div>
                </div>

                <div class="summary-row">
                    <div class="summary-label"><i class="bi bi-calendar-event"></i> Fecha</div>
                    <div class="text-end">
                        <div class="summary-value" id="sum_date"></div>
                        <div class="summary-value text-primary" id="sum_time"></div>
                        <a href="#" class="btn-edit" data-step="4">Cambiar</a>
                    </div>
                </div>

                <div class="summary-row">
                    <div class="summary-label"><i class="bi bi-person-badge"></i> Profesional</div>
                    <div class="text-end">
                        <div class="summary-value" id="sum_professional"></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-2 d-flex gap-3">
                <button type="button" id="prevBtnFinal" class="btn btn-outline-secondary flex-grow-1 py-2">Volver</button>
                <button type="submit" id="submitBtn" class="btn btn-primary flex-grow-1 py-2 fw-bold">Confirmar Reserva</button>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-5" id="navButtons">
          <button type="button" id="prevBtn" class="btn btn-outline-secondary px-4 rounded-pill" style="visibility:hidden;">
            <i class="bi bi-arrow-left me-1"></i> Atrás
          </button>
          <button type="button" id="nextBtn" class="btn btn-primary px-4 rounded-pill">
            Siguiente <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </div>

      </form>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const steps = document.querySelectorAll('.step-item');
      const contents = document.querySelectorAll('.step-content');
      const progressLine = document.getElementById('progressLine');
      
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      const prevBtnFinal = document.getElementById('prevBtnFinal'); // boton volver en el ultimo paso
      

      //lista de clinicas
      const clinic = document.getElementById('clinic');
      

      let currentStep = 1;
      const totalSteps = 5;

      //get all clinics

        fetch('/clinicas-open')
        .then(response => response.json())
        .then(data => {
            data.forEach(clinica => {
                const option = document.createElement('option');
                option.value = clinica.nombre;
                option.textContent = clinica.nombre;
                clinic.appendChild(option);
            });
            if(data.length === 0){
                const option = document.createElement('option');
                option.value = "";
                option.classList.add("text-dark")
                option.textContent = "No hay clínicas disponibles";
                clinic.appendChild(option);
            }
        })

        .catch(error => {
          console.error('Error fetching clinics:', error);
          const option = document.createElement('option');
          option.value = "";
          option.classList.add("text-dark")
          option.textContent = "Error al cargar clínicas";
          clinic.appendChild(option);
        });
        
        //get servicios de la clinica con su id

            

      // Inputs clave
      const clinicSelect = document.getElementById('clinic');
      const clinicInfo = document.getElementById('clinicInfo');
      const clinicAddressDisplay = document.getElementById('clinicAddressDisplay');
      
      const serviceRadios = document.getElementsByName('service');
      const medicalOptions = document.getElementById('medical-options');
      
      // Data Mock
      const clinicAddresses = {
        "Vetalia - Condesa": "Av Nuevo León 155, Hipódromo, CDMX",
        "Clínica Laika Norte": "Av Norte 200, Col. Norte",
        "Clínica Laika Sur": "Av Sur 50, Col. Sur"
      };

      // --- LOGICA UI ---
      
      // Mostrar info dirección al cambiar clinica
      clinicSelect.addEventListener('change', function(){
          const val = this.value;
          if(val && clinicAddresses[val]){
              clinicInfo.style.display = 'block';
              clinicAddressDisplay.textContent = clinicAddresses[val];
          } else {
              clinicInfo.style.display = 'none';
          }
      });

      // Mostrar sub-opciones medicas
      Array.from(serviceRadios).forEach(radio => {
          radio.addEventListener('change', function(){
              if(this.value === 'Visita médica'){
                  medicalOptions.classList.remove('d-none');
              } else {
                  medicalOptions.classList.add('d-none');
              }
          });
      });

      // --- NAVEGACION ---
      function updateStepperUI(step) {
          // Barra de progreso (simple cálculo: paso actual / total-1 * 100)
          const percentage = ((step - 1) / (totalSteps - 1)) * 100;
          progressLine.style.width = percentage + "%";

          steps.forEach(s => {
              const sIndex = parseInt(s.dataset.step);
              s.classList.remove('active', 'completed');
              if(sIndex < step) s.classList.add('completed');
              if(sIndex === step) s.classList.add('active');
          });

          contents.forEach(c => {
              const cIndex = parseInt(c.dataset.step);
              if(cIndex === step) {
                  c.classList.remove('d-none');
              } else {
                  c.classList.add('d-none');
              }
          });

          // Control botones
          if(step === 1) prevBtn.style.visibility = 'hidden';
          else prevBtn.style.visibility = 'visible';

          // Ocultar botones generales en el paso final (resumen)
          if(step === totalSteps) {
              document.getElementById('navButtons').style.display = 'none';
              populateSummary();
          } else {
              document.getElementById('navButtons').style.display = 'flex';
          }
      }

      nextBtn.addEventListener('click', () => {
          if(!validateStep(currentStep)) return; // Pequeña validación básica
          if(currentStep < totalSteps) {
              currentStep++;
              updateStepperUI(currentStep);
          }
      });

      prevBtn.addEventListener('click', () => {
          if(currentStep > 1) {
              currentStep--;
              updateStepperUI(currentStep);
          }
      });
      
      if(prevBtnFinal) {
          prevBtnFinal.addEventListener('click', () => {
              currentStep--;
              updateStepperUI(currentStep);
          });
      }

      // Links de "Modificar" en el resumen
      document.querySelectorAll('.btn-edit').forEach(btn => {
          btn.addEventListener('click', (e) => {
              e.preventDefault();
              const target = parseInt(btn.dataset.step);
              currentStep = target;
              updateStepperUI(currentStep);
          });
      });

      function validateStep(step) {
          // Validación simple para el ejemplo
          if(step === 1 && !clinicSelect.value) {
              alert("Por favor selecciona una clínica");
              return false;
          }
          if(step === 2) {
              let selected = false;
              serviceRadios.forEach(r => { if(r.checked) selected = true; });
              if(!selected) {
                  alert("Selecciona un servicio");
                  return false;
              }
          }
          if(step === 3 && !document.getElementById('pet_name').value) {
              alert("Ingresa el nombre de la mascota");
              return false;
          }
          // Puedes agregar más validaciones
          return true;
      }

      // --- CALENDARIO LOGIC (Simplificada) ---
      const calendarEl = document.getElementById('calendar');
      const headerEl = document.getElementById('calendarHeader');
      const timeSlotsEl = document.getElementById('timeSlots');
      const dateFeedback = document.getElementById('dateFeedback');
      
      const now = new Date();
      const monthNames = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
      const dayLetters = ["L","M","M","J","V","S","D"];
      
      headerEl.textContent = `${monthNames[now.getMonth()]} ${now.getFullYear()}`;
      
      // Dibujar letras
      dayLetters.forEach(l => {
          const div = document.createElement('div');
          div.className = 'day-name';
          div.textContent = l;
          calendarEl.appendChild(div);
      });
      
      // Dibujar dias (Dummy logic - asumiendo inicio en dia X)
      // Ajustar esto según el mes real
      for(let i=0; i<3; i++) calendarEl.appendChild(document.createElement('div')); // padding vacio
      
      let selectedDayEl = null;
      let selectedDateText = "";
      let selectedTimeText = "";

      for(let d=1; d<=30; d++){
          const day = document.createElement('div');
          day.className = 'day-number';
          day.textContent = d;
          day.onclick = () => {
              if(selectedDayEl) selectedDayEl.classList.remove('active');
              day.classList.add('active');
              selectedDayEl = day;
              
              // Mostrar horarios
              selectedDateText = `${d} de ${monthNames[now.getMonth()]}`;
              dateFeedback.innerHTML = `<i class="bi bi-calendar-check-fill text-success"></i> ${selectedDateText}`;
              dateFeedback.classList.remove('alert-light');
              dateFeedback.classList.add('alert-success');
              
              document.getElementById('timeContainer').classList.remove('d-none');
              generateTimes();
          };
          calendarEl.appendChild(day);
      }

      function generateTimes() {
          const times = ["09:00", "09:30", "10:00", "11:30", "15:00", "16:30"];
          timeSlotsEl.innerHTML = "";
          times.forEach(t => {
              const btn = document.createElement('button');
              btn.type = "button";
              btn.className = "time-btn";
              btn.textContent = t;
              btn.onclick = () => {
                  document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                  btn.classList.add('active');
                  selectedTimeText = t;
                  // Auto avance opcional
                  // currentStep++; updateStepperUI(currentStep);
              };
              timeSlotsEl.appendChild(btn);
          });
      }

      // --- RESUMEN ---
      function populateSummary() {
          // Clinica
          document.getElementById('sum_clinic').textContent = clinicSelect.value || 'No seleccionada';
          document.getElementById('sum_clinic_address').textContent = clinicAddresses[clinicSelect.value] || '';
          
          // Servicio (Radio)
          let srv = "";
          serviceRadios.forEach(r => { if(r.checked) srv = r.value; });
          document.getElementById('sum_service').textContent = srv || 'No seleccionado';
          
          const medReason = document.getElementById('medical_reason').value;
          document.getElementById('sum_service_sub').textContent = (srv === 'Visita médica') ? medReason : '';

          // Mascota
          const pName = document.getElementById('pet_name').value;
          // Especie
          let species = "";
          document.getElementsByName('species').forEach(r => { if(r.checked) species = r.value; });
          
          document.getElementById('sum_pet').textContent = pName;
          document.getElementById('sum_pet_sub').textContent = `${species} - ${document.getElementById('breed').value || ''}`;

          // Fecha
          document.getElementById('sum_date').textContent = selectedDateText || 'Pendiente';
          document.getElementById('sum_time').textContent = selectedTimeText || '';
          
          // Profesional
          const prof = document.getElementById('professional').value;
          document.getElementById('sum_professional').textContent = prof || 'Cualquiera disponible';
      }

    });
  </script>
</body>
</html>