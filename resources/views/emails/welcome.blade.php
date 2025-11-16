<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Bienvenido a VetCare</title>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f6f8fa;
      margin: 0;
      padding: 2rem;
      color: #333;
    }
    .card {
      max-width: 480px;
      margin: 0 auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 20px rgba(0,0,0,0.1);
      padding: 2rem;
    }
    .brand {
      text-align: center;
      color: #3A7CA5;
      font-weight: 600;
      font-size: 1.4rem;
      margin-bottom: 1rem;
    }
    .btn {
      display: inline-block;
      background: #3A7CA5;
      color: #ffffff;
      padding: 0.8rem 1.4rem;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
    }
    .btn:hover {
      background: #2f6485;
    }
    .footer {
      text-align: center;
      margin-top: 2rem;
      font-size: .85rem;
      color: #777;
    }
  </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="card">
    <div class="brand">🐾 VetCare</div>
    <h2>¡Bienvenido{{ isset($user->nombre) ? ' ' . e($user->nombre) : '' }}!</h2>
    <p>
      Tu cuenta se ha creado correctamente. Estamos felices de tenerte en VetCare.
    </p>
    <p>
      Desde tu cuenta podrás registrar tus mascotas, agendar citas y gestionar tu dispensador automático.
    </p>

    @if(!empty($url))
      <p style="text-align:center;">
        <a href="https://play.google.com/store/apps/details?id=com.jademajesty.laikaapp&hl=en-US&ah=cR3SjTQqCF2r67ioLxobDpW6xQ8" class="btn">Ir a VetCare</a>
      </p>
    @endif

    <p>
      Si no reconoces este registro, por favor ignora este mensaje.
    </p>

    <div class="footer">
      © {{ date('Y') }} VetCare. Todos los derechos reservados.
    </div>
  </div>
  
</body>
</html>
