<!doctype html>
<html lang="es" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laika - Términos y condiciones</title>

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
      box-shadow: 0 12px 30px -10px rgba(0, 0, 0, .15);
    }

    .card-policy h3 {
      color: var(--brand-dark);
      font-weight: 700;
      font-size: 1.25rem;
      margin-top: 1.5rem;
    }

    .card-policy p,
    .card-policy li {
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
      <h1 class="display-6 fw-bold mb-2">Términos y Condiciones</h1>
      <p class="lead mb-0">Tu uso de Laika implica la aceptación de estos términos.</p>
    </div>
  </section>

  <!-- Contenido -->
  <section class="py-5">
    <div class="container">
<div class="card card-policy p-4 p-md-5">
  <p><strong>Última actualización: 15 de Noviembre de 2025</strong></p>
  <h3 class="section-title mb-3">Términos y Condiciones</h3>

  <p>
    Los presentes Términos y Condiciones regulan el uso de la aplicación, sitio web y servicios ofrecidos por <strong>Laika</strong>,
    un ecosistema tecnológico diseñado para apoyar a propietarios de mascotas y clínicas veterinarias mediante gestión de citas,
    historial clínico y control de dispensadores inteligentes de alimento y agua.
  </p>

  <p>
    Al descargar, instalar, registrarse o utilizar la aplicación Laika, el usuario acepta plenamente estos Términos y Condiciones.
    Si no está de acuerdo, deberá abstenerse de utilizar la plataforma.
  </p>

  <h3>1. Propósito de la Aplicación</h3>
  <p>Laika tiene como finalidad:</p>
  <ul>
    <li>Facilitar la gestión y programación de citas veterinarias.</li>
    <li>Brindar acceso seguro al historial clínico digital de las mascotas.</li>
    <li>Permitir la configuración, monitoreo y control de un dispensador automático IoT de alimento y agua.</li>
    <li>Mejorar la comunicación entre usuarios, veterinarios y clínicas afiliadas.</li>
  </ul>

  <h3>2. Uso Autorizado</h3>
  <p>El usuario se compromete a utilizar la aplicación exclusivamente para fines personales y legales. Queda prohibido:</p>
  <ul>
    <li>Manipular, desactivar o modificar indebidamente los dispositivos IoT vinculados al servicio.</li>
    <li>Intentar vulnerar la seguridad o acceder a información de otros usuarios.</li>
    <li>Realizar ingeniería inversa, descompilar o copiar la aplicación sin autorización.</li>
    <li>Utilizar la plataforma para actividades ilícitas, fraudulentas o que afecten a terceros.</li>
    <li>Crear cuentas falsas o proporcionar información incorrecta.</li>
  </ul>

  <h3>3. Obligaciones del Usuario</h3>
  <ul>
    <li>Mantener sus credenciales de acceso de forma segura.</li>
    <li>Proporcionar información real, actualizada y verificable.</li>
    <li>Notificar al soporte si detecta un acceso no autorizado a su cuenta.</li>
    <li>No permitir que terceros utilicen su cuenta sin supervisión.</li>
  </ul>

  <h3>4. Funcionamiento del Dispensador IoT</h3>
  <p>El usuario reconoce que:</p>
  <ul>
    <li>El dispensador es un dispositivo auxiliar y no reemplaza la supervisión directa hacia la mascota.</li>
    <li>El funcionamiento depende de la conexión a internet, energía eléctrica y configuración adecuada.</li>
    <li>Laika no garantiza alimentación automática en caso de fallas externas o saturación de red.</li>
  </ul>

  <h3>5. Limitación de Responsabilidad</h3>
  <p>Laika no será responsable por:</p>
  <ul>
    <li>Fallas técnicas, interrupciones del servicio o pérdida de información ocasionada por factores externos.</li>
    <li>Daños derivados del mal uso de la aplicación, negligencia del usuario o manipulación indebida del dispensador.</li>
    <li>Problemas ocasionados por conexión deficiente, dispositivos incompatibles o falta de mantenimiento del usuario.</li>
  </ul>

  <h3>6. Propiedad Intelectual</h3>
  <p>
    Todo el contenido de Laika (software, API, diseños, logotipos, modelos IoT, bases de datos, textos e interfaces)
    es propiedad exclusiva de la empresa. Su uso se otorga bajo una licencia personal, limitada, no exclusiva,
    revocable y no transferible. No se otorgan derechos distintos a los expresamente permitidos.
  </p>

  <h3>7. Privacidad y Protección de Datos</h3>
  <p>
    El tratamiento de la información personal se rige por nuestra Política de Privacidad.  
    Al usar la aplicación, el usuario acepta dicho tratamiento.
  </p>

  <h3>8. Eliminación de Cuenta y Datos</h3>
  <p>
    El usuario puede solicitar la eliminación definitiva de su cuenta en cualquier momento.  
    Al hacerlo:
  </p>
  <ul>
    <li>Se elimina toda la información personal y datos asociados.</li>
    <li>No se conserva ningún dato en copias de seguridad o registros internos.</li>
    <li>Los dispositivos IoT se desvinculan permanentemente.</li>
    <li>Los datos solo se conservarán si existiera una obligación legal vigente.</li>
  </ul>
  <p>
    La eliminación es irreversible y Laika no podrá recuperar información posteriormente.
  </p>

  <h3>9. Datos de Menores de Edad</h3>
  <p>
    Los menores solo pueden utilizar la aplicación bajo supervisión y autorización expresa de un padre o tutor,
    quien será responsable del uso de la cuenta.
  </p>

  <h3>10. Modificaciones a los Términos</h3>
  <p>
    Laika podrá actualizar estos Términos y Condiciones en cualquier momento para reflejar cambios legales,
    técnicos o funcionales. Se avisará con antelación razonable mediante la aplicación o correo electrónico.
  </p>

  <h3>11. Suspensión o Cancelación de Servicios</h3>
  <p>
    Laika puede suspender temporal o permanentemente una cuenta cuando:
  </p>
  <ul>
    <li>Se detecte actividad fraudulenta o violación a estos términos.</li>
    <li>Exista riesgo de seguridad para otros usuarios o para la infraestructura.</li>
    <li>Haya mal uso del dispensador IoT o manipulación indebida del sistema.</li>
  </ul>

  <h3>12. Contacto</h3>
  <p>
    Para dudas, aclaraciones o soporte técnico, puede contactarnos en:  
    <a href="mailto:help.vetpet@jademajesty.com">help.vetpet@jademajesty.com</a>
  </p>

  <h3>13. Aceptación</h3>
  <p>
    Al usar la aplicación, el panel administrativo o registrar un dispositivo, usted declara haber leído y aceptado estos
    Términos y Condiciones en su totalidad.
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
          <p class="small">Lun–Vie: 9:00–20:00<br>Sáb: 9:00–14:00<br><span class="fw-semibold text-primary">Domingo
              cerrado</span></p>
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