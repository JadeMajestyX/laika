<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laika - Clínica Veterinaria</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand: #3A7CA5;
      --brand-dark: #2f6485;
      --bs-primary: var(--brand);
      --bs-link-color: var(--brand);
      --bs-link-hover-color: var(--brand-dark);
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }

    /* Navbar */
    .navbar {
      background: linear-gradient(90deg, var(--brand), var(--brand-dark));
    }
    .navbar .nav-link {
      color: #fff;
      font-weight: 500;
      transition: color 0.2s;
    }
    .navbar .nav-link:hover {
      color: #d7eaf4;
    }

    /* Hero */
    .hero {
      position: relative;
      background: url('{{ asset('images/home_veterinarios.jpg') }}') center/cover no-repeat;
      color: #fff;
      height: 90vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }
    .hero::after {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(58, 124, 165, 0.7);
    }
    .hero-content {
      position: relative;
      z-index: 1;
      max-width: 700px;
    }

    /* Cards */
    .card {
      border-radius: 1rem;
      border: 1px solid #e0e0e0;
      transition: transform .3s, box-shadow .3s;
    }
    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 16px 35px -5px rgba(0, 0, 0, 0.15);
    }

    /* Section titles */
    .section-title {
      color: var(--brand);
      font-weight: 700;
    }

    /* Footer */
    footer {
      background-color: #f0f4f7;
      border-top: 1px solid #dbe2e8;
    }
    footer a {
      color: var(--brand);
      text-decoration: none;
    }
    footer a:hover {
      color: var(--brand-dark);
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
        <i class="bi bi-heart-pulse-fill me-2"></i> Laika
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="#dispensador">Dispensador</a></li>
          <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
          <li class="nav-item"><a class="nav-link" href="#equipo">Equipo</a></li>
          <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
        </ul>
        <a href="{{ route('agendar.cita') }}" class="btn btn-light ms-lg-3 fw-semibold">Agendar cita</a>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section id="inicio" class="hero">
    <div class="hero-content text-white">
      <h1 class="display-4 fw-bold mb-3">Cuidamos lo que más amas ❤️</h1>
      <p class="lead mb-4">En Laika, tu mascota recibe atención profesional, cariño y tecnología avanzada para su bienestar.</p>
      <a href="#servicios" class="btn btn-light btn-lg fw-semibold">Conoce nuestros servicios</a>
    </div>
  </section>

  <!-- ¿Quiénes somos? -->
  <section class="py-5 container">
    <div class="row align-items-center g-4">
      <div class="col-md-6">
        <img src="{{ asset('images/VetPet.png') }}"
             alt="Clínica veterinaria moderna" class="img-fluid rounded-4 shadow-sm">
      </div>
      <div class="col-md-6">
        <h2 class="section-title mb-3">¿Quiénes somos?</h2>
        <p>En <strong>Laika</strong> somos una clínica veterinaria comprometida con la salud de tus mascotas. Nuestro equipo ofrece atención médica de alta calidad, tratamientos especializados y un ambiente amigable.</p>
        <p>Además, integramos tecnología moderna en nuestros servicios, como dispensadores automáticos, monitoreo remoto y una app para agendar tus citas con facilidad.</p>
      </div>
    </div>
  </section>
  <!-- Dispensador Automático -->
<section id="dispensador" class="py-5 container">
  <div class="row align-items-center g-4">
    <div class="col-md-6 order-md-2">
      <img src="https://images.unsplash.com/photo-1592194996308-7b43878e84a6?auto=format&fit=crop&q=80&w=1200" 
           alt="Dispensador automático de comida para mascotas" class="img-fluid rounded-4 shadow-sm">
    </div>
    <div class="col-md-6 order-md-1">
      <h2 class="section-title mb-3">Dispensador Automático</h2>
      <p>En <strong>Laika</strong> contamos con dispensadores automáticos de comida y agua que permiten cuidar la alimentación de tu mascota incluso cuando no estás en casa.</p>
      <ul class="list-unstyled">
        <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Control remoto desde nuestra app.</li>
        <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Porciones personalizadas según el tipo y peso de tu mascota.</li>
        <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Monitoreo en tiempo real del nivel de comida y agua.</li>
        <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Alertas automáticas cuando se requiere recarga.</li>
      </ul>
      {{-- <a href="#contacto" class="btn btn-primary mt-3">Consulta disponibilidad</a> --}}
    </div>
  </div>
</section>




  <!-- Servicios -->
  <section id="servicios" class="py-5 bg-body-tertiary">
    <div class="container text-center">
      <h2 class="section-title mb-4">Nuestros Servicios</h2>
      <p class="text-muted mb-5">Atención veterinaria integral con profesionales apasionados por el bienestar animal.</p>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100">
            <img src="{{ asset('images/consultas_medicas.png') }}" class="card-img-top" alt="Consulta veterinaria">
            <div class="card-body">
              <h5 class="card-title text-primary">Consultas médicas</h5>
              <p class="card-text">Evaluaciones generales, diagnóstico y tratamiento para tus mascotas.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="{{ asset('images/vacunacion.png') }}" class="card-img-top" alt="Vacunación">
            <div class="card-body">
              <h5 class="card-title text-primary">Vacunación y desparasitación</h5>
              <p class="card-text">Previene enfermedades y asegura la salud duradera de tus amigos peludos.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="{{ asset('images/cirugia.png') }}" class="card-img-top" alt="Cirugía veterinaria">
            <div class="card-body">
              <h5 class="card-title text-primary">Cirugías menores</h5>
              <p class="card-text">Procedimientos seguros con equipamiento moderno y profesionales experimentados.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Equipo -->
  <section id="equipo" class="py-5 container">
    <div class="text-center mb-5">
      <h2 class="section-title mb-3">Nuestro equipo</h2>
      <p class="text-muted">Conoce a los veterinarios y técnicos que hacen de Laika un lugar especial.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card text-center border-0 shadow-sm p-4">
          <img src="https://images.unsplash.com/photo-1607746882042-944635dfe10e?auto=format&fit=crop&q=80&w=400"
               class="rounded-circle mx-auto mb-3" width="100" height="100" alt="Dra. Pérez">
          <h5 class="fw-semibold text-primary">Dra. Ana Pérez</h5>
          <p class="text-muted small">Cirujana veterinaria</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center border-0 shadow-sm p-4">
          <img src="{{ asset('images/mancillas.png') }}"
               class="rounded-circle mx-auto mb-3" width="100" height="100" alt="Dr. Mancillas">
          <h5 class="fw-semibold text-primary">Dr. Gabriel Mancillas</h5>
          <p class="text-muted small">Especialista en salud animal</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center border-0 shadow-sm p-4">
          <img src="https://images.unsplash.com/photo-1599577183888-5f46d4b2f46b?auto=format&fit=crop&q=80&w=400"
               class="rounded-circle mx-auto mb-3" width="100" height="100" alt="Téc. Gómez">
          <h5 class="fw-semibold text-primary">Laura Gómez</h5>
          <p class="text-muted small">Asistente veterinaria</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Aplicación Móvil -->
<section id="app" class="py-5 bg-light">
  <div class="container text-center">
    <h2 class="section-title mb-3">¡Lleva Laika en tu bolsillo!</h2>
    <p class="mb-4">Con nuestra app móvil, podrás agendar citas, monitorear la alimentación de tu mascota y recibir alertas en tiempo real.</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="#" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
        {{-- <i class="bi bi-google-play"></i> Google Play --}}
        Descargar App
      </a>
    <div class="hero-content text-white">
      <h1 class="display-4 fw-bold mb-3">Cuidamos lo que más amas ❤️</h1>
      <p class="lead mb-4">En Laika, tu mascota recibe atención profesional, cariño y tecnología avanzada para su bienestar.</p>
      <a href="#servicios" class="btn btn-light btn-lg fw-semibold">Conoce nuestros servicios</a>
    </div>
  </section>

      {{-- <a href="#" class="btn btn-dark btn-lg d-flex align-items-center gap-2">
        <i class="bi bi-apple"></i> App Store
      </a> --}}
    </div>
    <div class="mt-4">
      <img src="https://images.unsplash.com/photo-1585079549630-5f74a2fa065b?auto=format&fit=crop&q=80&w=600"
           alt="App móvil Laika" class="img-fluid rounded-4 shadow-sm">
    </div>
  </div>
</section>


  <!-- CTA -->
  <section class="text-center py-5 text-white" style="background: linear-gradient(90deg,var(--brand),var(--brand-dark));">
    <div class="container">
      <h2 class="fw-bold mb-3">¿Listo para agendar una cita?</h2>
      <p class="mb-4">Tu mascota merece lo mejor. Agenda hoy mismo con nuestros expertos veterinarios.</p>
      <a href="{{ route('agendar.cita') }}" class="btn btn-light fw-semibold">Agendar Cita</a>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contacto" class="pt-5">
    <div class="container pb-4">
      <div class="row g-4">
        <div class="col-md-4">
          <h5 class="fw-bold text-primary">Contacto</h5>
          <ul class="list-unstyled small">
            <li><i class="bi bi-telephone me-2 text-primary"></i> +52 312 000 0000</li>
            <li><i class="bi bi-envelope me-2 text-primary"></i> help.vetpet@jademajesty.com</li>
            <li><i class="bi bi-geo-alt me-2 text-primary"></i> Manzanillo, Colima</li>
          </ul>
        </div>
        <div class="col-md-4">
          <h5 class="fw-bold text-primary">Horarios</h5>
          <p class="small">Lun–Vie: 9:00–20:00<br>Sáb: 9:00–14:00<br><span class="fw-semibold text-primary">Domingo cerrado</span></p>
        </div>
        <div class="col-md-4">
          <h5 class="fw-bold text-primary">Síguenos</h5>
          <div class="d-flex gap-3 fs-4 text-primary">
            <i class="bi bi-facebook"></i>
            <i class="bi bi-instagram"></i>
            <i class="bi bi-twitter-x"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="text-center py-3 border-top small text-muted">
      © {{ date('Y') }} Laika · Todos los derechos reservados
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>























