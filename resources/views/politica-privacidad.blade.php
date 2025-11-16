<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laika - Política de Privacidad</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand: #3A7CA5;
      --brand-dark: #2f6485;
      --bs-primary: var(--brand);
      --bs-link-color: var(--brand);
      --bs-link-hover-color: var(--brand-dark);
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
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
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      background: linear-gradient(90deg, var(--brand), var(--brand-dark));
      margin-top: 56px; 
    }
    .hero .hero-content {
      position: relative;
      z-index: 1;
      max-width: 840px;
      padding: 2rem 1.25rem;
    }

    /* Card */
    .card-policy {
      border-radius: 1rem;
      border: 1px solid #e0e0e0;
      background: #fff;
      box-shadow: 0 12px 30px -10px rgba(0,0,0,.15);
    }
    .card-policy h3 {
      color: var(--brand-dark);
      font-weight: 700;
      font-size: 1.25rem;
      margin-top: 1.5rem;
    }
    .card-policy p, .card-policy li {
      color: #555;
      font-size: 1rem;
      line-height: 1.7;
    }
    .section-title {
      color: #0b2d42;
      letter-spacing: .2px;
    }

    /* Footer */
    footer {
      background-color: #f0f4f7;
      border-top: 1px solid #dbe2e8;
    }
    footer a { color: var(--brand); text-decoration: none; }
    footer a:hover { color: var(--brand-dark); }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ url('/') }}">
        <i class="bi bi-heart-pulse-fill me-2"></i> Laika
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="{{ url('/#inicio') }}">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ url('/#dispensador') }}">Dispensador</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ url('/#servicios') }}">Servicios</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ url('/#equipo') }}">Equipo</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ url('/#contacto') }}">Contacto</a></li>
        </ul>
        <a href="{{ url('/#contacto') }}" class="btn btn-light ms-lg-3 fw-semibold">Agendar cita</a>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-content">
      <h1 class="display-6 fw-bold mb-2">Política de Privacidad</h1>
      <p class="lead mb-0">Transparencia y protección de tus datos personales en Laika.</p>
    </div>
  </section>

  <!-- Contenido -->
  <section class="py-5">
    <div class="container">
<div class="card card-policy p-4 p-md-5">
  <h3 class="section-title mb-3">Política de Privacidad</h3>

  <h3>1. Responsable del tratamiento</h3>
  <p>
    El responsable del tratamiento de los datos personales recabados a través de esta aplicación es <strong>Laika</strong>.  
    Para consultas o más información, puede contactarnos en  
    <a href="mailto:help.vetpet@jademajesty.com">help.vetpet@jademajesty.com</a>.
  </p>

  <h3>2. Datos que recopilamos</h3>
  <ul>
    <li><strong>Datos de usuario:</strong> nombre, correo electrónico, teléfono, fecha de nacimiento, fotografía de perfil y contraseña (encriptada).</li>
    <li><strong>Datos de mascotas:</strong> nombre, foto, especie, edad, peso, historial médico y vacunación.</li>
    <li><strong>Datos del dispensador:</strong> horarios, porciones, estadísticas de uso y estado de conexión.</li>
    <li><strong>Datos técnicos:</strong> dirección IP, tipo de dispositivo, sistema operativo, fecha y hora de acceso.</li>
  </ul>

  <h3>3. Finalidad del tratamiento</h3>
  <ul>
    <li>Administrar citas veterinarias y servicios ofrecidos por Laika.</li>
    <li>Registrar y consultar el historial clínico digital de las mascotas.</li>
    <li>Controlar y monitorear el dispensador inteligente de alimento.</li>
    <li>Mejorar la experiencia, seguridad y rendimiento de la aplicación.</li>
    <li>Enviar notificaciones relacionadas con servicios y funcionamiento del dispensador (cuando el usuario lo permita).</li>
  </ul>

  <h3>4. Eliminación total de la cuenta y datos</h3>
  <p>
    El usuario puede solicitar en cualquier momento la <strong>eliminación definitiva de su cuenta</strong>.  
    Cuando esto sucede:
  </p>
  <ul>
    <li>Se elimina de manera irreversible toda la información personal del usuario.</li>
    <li>Se eliminan los registros de sus mascotas, citas, historial clínico y configuraciones del dispensador.</li>
    <li>No se conserva ningún dato en respaldos, logs ni archivos internos.</li>
    <li>Los datos solo podrán mantenerse si existe una obligación legal de conservación; de no ser así, se eliminan totalmente.</li>
  </ul>
  <p>
    Una vez eliminada la cuenta, <strong>Laika no conserva ningún dato personal asociado al usuario</strong>.
  </p>

  <h3>5. Cesión y transferencia de datos</h3>
  <p>
    Laika no vende, intercambia ni alquila datos personales.  
    La información únicamente podrá compartirse con proveedores estrictamente necesarios para el funcionamiento de la aplicación (hosting, base de datos, envío de correos, servicios en la nube) y que estén obligados contractualmente a garantizar la confidencialidad y seguridad de los datos.
  </p>

  <h3>6. Medidas de seguridad</h3>
  <ul>
    <li>Cifrado de datos en tránsito mediante HTTPS.</li>
    <li>Contraseñas protegidas con algoritmos de hash y sal.</li>
    <li>Controles de acceso, autenticación y monitoreo de actividad.</li>
    <li>Respaldo seguro y mitigación ante pérdida de información.</li>
    <li>Análisis continuo para prevenir accesos no autorizados.</li>
  </ul>

  <h3>7. Conservación de datos</h3>
  <p>
    Los datos se conservarán únicamente mientras la cuenta esté activa o mientras sea estrictamente necesario para cumplir las finalidades descritas.  
    Cuando los datos ya no sean necesarios, se eliminarán o se anonimizarán siguiendo estándares de seguridad.
  </p>

  <h3>8. Derechos del usuario</h3>
  <p>El usuario puede ejercer los siguientes derechos en cualquier momento:</p>
  <ul>
    <li><strong>Acceso:</strong> conocer los datos que Laika almacena.</li>
    <li><strong>Rectificación:</strong> modificar datos incorrectos o incompletos.</li>
    <li><strong>Supresión:</strong> solicitar la eliminación total de la cuenta y todos los datos asociados.</li>
    <li><strong>Oposición:</strong> rechazar ciertos tratamientos no esenciales.</li>
    <li><strong>Portabilidad:</strong> solicitar una copia de sus datos en formato digital.</li>
  </ul>

  <h3>9. Cambios en la Política</h3>
  <p>
    Laika podrá actualizar esta Política de Privacidad para reflejar cambios legales, funcionales o de seguridad.  
    Cuando existan modificaciones relevantes, se notificará al usuario con anticipación mediante la aplicación o correo electrónico.
  </p>

  <h3>10. Contacto</h3>
  <p>
    Para ejercer sus derechos o hacer consultas sobre esta política, puede comunicarse a  
    <a href="mailto:help.vetpet@jademajesty.com">help.vetpet@jademajesty.com</a>.
  </p>

  <h3>11. Aceptación</h3>
  <p>
    Al utilizar la aplicación, el panel administrativo o registrar un dispositivo, usted declara haber leído, comprendido y aceptado esta Política de Privacidad.
  </p>
</div>

    </div>
  </section>

  <!-- Footer -->
  <footer id="contacto" class="pt-5">
    <div class="container pb-4">
      <div class="row g-4">
        <div class="col-md-4">
          <h5 class="fw-bold text-primary">Contacto</h5>
          <ul class="list-unstyled small">
            <li><i class="bi bi-telephone me-2 text-primary"></i> +52 314 160 9870</li>
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