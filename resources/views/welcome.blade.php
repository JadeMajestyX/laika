<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Laika</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    :root{
      --laika-primary:#6f42c1;   /* morado principal */
      --laika-primary-700:#5b2eae;/* morado oscuro */
      --laika-primary-100:#efe8fb;/* claro para fondos */
      --media-h: 150px;           /* altura uniforme (ajústala si quieres más/menos) */
    }
    *{ box-sizing: border-box; }
    html, body{ height:100%; margin:0; }
    body{
      min-height:100vh;
      background: radial-gradient(1200px 600px at 20% -10%, var(--laika-primary-100), #ffffff 60%);
    }
    /* Contenedor a pantalla completa sin márgenes blancos */
    .page{ min-height:100vh; width:100%; padding:0; }

    /* Header morado (arriba recto, curva solo abajo) */
    .laika-header{
      position: relative;
      padding-top: 3.5rem;
      padding-bottom: 2.25rem;
      text-align: center;
      color: #fff;
      background: linear-gradient(135deg, var(--laika-primary), var(--laika-primary-700));
      border-radius: 0 0 2.25rem 2.25rem;
      overflow: hidden;
    }
    .laika-header::after{
      content:"";
      position:absolute; inset:0;
      background: radial-gradient(500px 160px at 80% -40%, rgba(255,255,255,.25), transparent 60%);
      pointer-events:none;
    }
    .logo-badge{
      width: 88px; height: 88px; border-radius: 50%;
      background: #fff; display: grid; place-items: center;
      margin: -52px auto 8px auto; border: 6px solid #fff;
      box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .logo-badge i{ font-size: 40px; color: var(--laika-primary); }
    .laika-title{ font-weight: 800; letter-spacing:.4px; }

    /* Cards / bloques */
    .section-wrap{ padding: 2rem 1.25rem 3rem; }
    @media (min-width: 992px){ .section-wrap{ padding: 3rem 2.5rem 4rem; } }

    .feature-card{
      border:1px solid rgba(111,66,193,.12);
      border-radius: 1rem;
      background: #fff;
      height: 100%;
    }
    .feature-card .card-header{
      background: transparent;
      border-bottom: 0;
      font-weight: 700;
      color:#333;
    }

    .icon-circle{
      width: 40px; height: 40px; border-radius: 50%;
      display:grid; place-items:center;
      background: var(--laika-primary-100);
      color: var(--laika-primary-700);
      flex: 0 0 auto;
    }

    .list-group-item{ border:0; padding:.5rem 0; }
    .contact-link{ text-decoration:none; }

    /* Utilidades */
    .small-muted{ color:#6c757d; font-size:.95rem; }

    /* === Imagenes uniformes y un poco más pequeñas === */
    .laika-media{
      height: var(--media-h);
      width: 100%;
      border-radius: .75rem;
      overflow: hidden;
    }
    .laika-media img{
      width: 100%;
      height: 100%;
      object-fit: cover;   /* recorta manteniendo proporción */
      object-position: center;
      display: block;
    }
  </style>
</head>
<body>
  <main class="page">
    <!-- Header -->
    <header class="laika-header">
      <div class="logo-badge"><i class="bi bi-heart-pulse"></i></div>
      <h2 class="laika-title mb-0">Laika</h2>
      <p class="mb-0 opacity-75">Clínica veterinaria</p>
    </header>

    <!-- Contenido -->
    <section class="section-wrap">
      <div class="row g-4 align-items-stretch">
        <!-- ¿Quiénes somos? -->
        <div class="col-12 col-lg-5">
          <div class="feature-card card h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="icon-circle"><i class="bi bi-question-circle"></i></span>
                <span class="fw-bold">¿Quiénes somos?</span>
              </div>
              <!-- Imagen ¿Quiénes somos? (misma altura que las demás) -->
              <div class="mb-3 laika-media">
                <img src="{{ asset('images/qui.jpeg') }}" alt="Laika clínica veterinaria en Manzanillo, Colima">
              </div>
              <p class="small-muted mb-0">
                Laika es una clínica veterinaria en Manzanillo, Colima. 
                Hacemos fácil el cuidado de tu mascota con atención profesional y 
                herramientas como nuestra app de citas y un dispensador 
                de comida automatizado.
              </p>
            </div>
          </div>
        </div>

        <!-- Dispensador automatizado -->
        <div class="col-12 col-sm-6 col-lg-4 d-flex">
          <div class="feature-card card flex-fill">
            <div class="card-header d-flex align-items-center gap-2">
              <span class="icon-circle"><i class="bi bi-robot"></i></span>
              <span>Dispensador automatizado</span>
            </div>
            <div class="card-body">
              <!-- Imagen Dispensador (misma altura) -->
              <div class="mb-3 laika-media">
                <img src="{{ asset('images/disi.jpg') }}" alt="Dispensador automatizado Laika">
              </div>
              <p class="small-muted">
                Sistema inteligente para dispensar alimento en cantidades
                exactas en horarios programados.
              </p>
            </div>
          </div>
        </div>

        <!-- Servicios (con iconos y sin Grooming) -->
        <div class="col-12 col-sm-6 col-lg-3 d-flex">
          <div class="feature-card card flex-fill">
            <div class="card-header d-flex align-items-center gap-2">
              <span class="icon-circle"><i class="bi bi-list-check"></i></span>
              <span>Servicios</span>
            </div>
            <div class="card-body">
              <!-- Imagen Servicios (misma altura) -->
              <div class="mb-3 laika-media">
                <img src="{{ asset('images/ser.jpg') }}" alt="Servicios veterinarios Laika">
              </div>
              <div class="row small text-secondary g-3">
                <div class="col-12 d-flex align-items-center gap-2">
                  <i class="bi bi-clipboard2-pulse"></i> Consultas
                </div>
                <div class="col-12 d-flex align-items-center gap-2">
                  <i class="bi bi-shield-plus"></i> Vacunación
                </div>
                <div class="col-12 d-flex align-items-center gap-2">
                  <i class="bi bi-heart-pulse"></i> Cirugías menores
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Horarios -->
        <div class="col-12 col-lg-4">
          <div class="feature-card card">
            <div class="card-header d-flex align-items-center gap-2">
              <span class="icon-circle"><i class="bi bi-clock-history"></i></span>
              <span>Horarios</span>
            </div>
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li class="list-group-item">Lun – Vie: 9:00–14:00, 16:00–20:00</li>
                <li class="list-group-item">Sábado: 9:00–14:00</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Contacto -->
        <div class="col-12 col-lg-4">
          <div class="feature-card card">
            <div class="card-header d-flex align-items-center gap-2">
              <span class="icon-circle"><i class="bi bi-telephone"></i></span>
              <span>Contacto</span>
            </div>
            <div class="card-body">
              <ul class="list-unstyled small mb-0">
                <li class="mb-2 d-flex gap-2 align-items-center">
                  <i class="bi bi-telephone"></i>
                  <a class="contact-link" href="tel:+523120000000">+52 312 000 0000</a>
                </li>
                <li class="mb-2 d-flex gap-2 align-items-center">
                  <i class="bi bi-envelope"></i>
                  <a class="contact-link" href="mailto:contacto@laika.vet">laika@gmail.com</a>
                </li>
                <li class="mb-2 d-flex gap-2 align-items-center">
                  <i class="bi bi-geo-alt"></i>
                  calle tal tal tal 
                </li>
              </ul>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>