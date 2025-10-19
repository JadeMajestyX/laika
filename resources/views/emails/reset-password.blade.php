<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Restablecer contraseña</title>
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
  color: white; /* <- Esto ya hace que el texto sea blanco */
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
</head>
<body>
  <div class="card">
    <div class="brand">🐾 VetCare</div>
    <h2>¿Olvidaste tu contraseña?</h2>
    <p>Hola {{ $user->nombre ?? 'usuario' }},</p>
    <p>Recibimos una solicitud para restablecer tu contraseña. Puedes hacerlo haciendo clic en el siguiente botón:</p>

<p style="text-align:center;">
  <a href="{{ $url }}" 
     style="display:inline-block; background:#3A7CA5; color:#ffffff; padding:0.8rem 1.4rem; border-radius:6px; text-decoration:none; font-weight:500;"
  >
    Restablecer contraseña
  </a>
</p>


    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>

    <div class="footer">
      © {{ date('Y') }} VetCare. Todos los derechos reservados.
    </div>
  </div>
</body>
</html>
