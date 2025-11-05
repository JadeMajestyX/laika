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
        <p><strong>Última actualización: 5 de Noviembre de 2025</strong></p>
        <h3 class="section-title mb-3">Términos y Condiciones</h3>

        <p>
          Laika es un ecosistema tecnológico destinado a mejorar la experiencia de los propietarios de mascotas y
          optimizar la gestión operativa de clínicas veterinarias. A través de la plataforma, el usuario podrá gestionar
          citas médicas, consultar historiales clínicos de sus mascotas y controlar remotamente un dispensador
          inteligente de alimento y agua para animales.

          Al descargar, instalar o utilizar la aplicación Laika, el usuario acepta plenamente los presentes Términos y
          Condiciones. Si no está de acuerdo con ellos, deberá abstenerse de utilizar la aplicación y sus servicios.
        </p>

        <h3>1. Proposito de la aplicación</h3>
        <p>
          Laika tiene como finalidad:
        <ul>
          <li>Facilitar la gestión de citas veterinarias.</li>
          <li>Brindar acceso al historial clínico de las mascotas.</li>
          <li>Permitir la programación y control de un dispensador automático de alimento mediante tecnología IoT</li>

        </ul>
        </p>

        <h3>2. Uso Autorizado</h3>
        <p>
          EL usuario se compromete a utilizar la aplicación de forma personal, responsable y conforme a la ley. Queda estrictamente prohibido:
        </p>
        <ul>
          <li>Manipular, alterar o usar de forma indebida los dispositivos IoT vinculados al sistema.</li>
          <li>Copiar, distribuir, descompilar o modificar la aplicación y sus componentes sin autorización.</li>
          <li>Usar la plataforma para actividades ilícitas, fraudulentas, malintencionadas o que afecten a terceros.</li>
          <li>Intentar acceder a información, cuentas o datos de otros usuarios sin autorización.</li>
        </ul>

        <h3>3. Obligaciones del Usuario</h3>
        <p>El usuario acepta:</p>
        <ul>
          <li>Mantener en secreto y proteger sus credenciales de acceso.</li>
          <li>Proveer información verídica, exacta y actualizada.</li>
          <li>Asumir responsabilidad por el uso que terceros hagan de su cuenta en caso de compartir voluntariamente sus accesos.</li>
        </ul>

        <h3>4. Limitación de Responsabilidad</h3>
        <p>
          Laika no se hace responsable por:
        </p>
        <ul>
          <li>Fallas técnicas, interrupciones del servicio, errores de conexión o pérdidas de información</li>
          <li>Daños derivados del uso inadecuado de la aplicación o de los dispositivos IoT asociados.</li>
          <li>Consecuencias por el uso excesivo del sistema sin intervención humana. <br>
            El dispensador IoT es un auxiliar tecnológico y no sustituye la atención directa del propietario hacia su mascota.
          </li>
        </ul>

        <h3>5. Propiedad Intelectual</h3>
        <p>Todo contenido, software, código, nombre comercial, logotipos, diseños e interfaces de Laika son propiedad exclusivamente de la empresa. Su uso se otorga únicamente bajo licencia personal, limitada, revocable y no transferible. El usuario no adquiere derechos sobre los activos intelectuales mencionados.</p>

        <h3>6. Mo</h3>
        <p>
          Los datos personales se conservarán únicamente durante el tiempo necesario para cumplir con los fines
          descritos,
          mientras la cuenta esté activa o según lo establecido por la ley. Una vez cumplido el plazo, los datos se
          eliminarán
          o anonimizarán de forma segura.
        </p>

        <h3>7. Datos de menores</h3>
        <p>
          Si el titular de los datos es menor de edad, el uso de la aplicación y el registro deben realizarse con el
          consentimiento
          y supervisión de un padre o tutor.
        </p>

        <h3>8. Cambios en la Política</h3>
        <p>
          Esta Política de Privacidad puede actualizarse. Cuando se realicen cambios significativos,
          se notificará a los usuarios mediante la aplicación o correo electrónico con antelación razonable.
        </p>

        <h3>9. Contacto</h3>
        <p>
          Para dudas o reportes sobre privacidad, escriba a
          <a href="mailto:help.vetpet@jademajesty.com">help.vetpet@jademajesty.com</a>.
        </p>

        <h3>10. Aceptación</h3>
        <p>
          Al usar la aplicación, el panel administrativo o registrar un dispositivo dispensador de alimento,
          usted acepta los términos de esta Política de Privacidad y consiente el tratamiento de sus datos conforme a lo
          aquí descrito.
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