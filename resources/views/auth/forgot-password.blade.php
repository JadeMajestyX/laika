<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recuperar Contraseña - VetCare</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Fuente -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand: #3A7CA5;
      --brand-dark: #2f6485;
    }
    body {
      background: linear-gradient(135deg, var(--brand), var(--brand-dark));
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .auth-card {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
      padding: 2.5rem;
      max-width: 420px;
      width: 100%;
    }
    .auth-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .6rem;
      margin-bottom: 1.5rem;
      color: var(--brand);
    }
    .auth-logo i {
      background: var(--brand);
      color: #fff;
      padding: .6rem;
      border-radius: .75rem;
      font-size: 1.3rem;
    }
    .form-label {
      font-weight: 500;
    }
    .btn-primary {
      background: var(--brand);
      border: none;
    }
    .btn-primary:hover {
      background: var(--brand-dark);
    }
    .text-muted {
      color: #6c757d !important;
    }
  </style>
</head>
<body>
  <div class="auth-card">
    <div class="auth-logo">
      <i class="bi bi-heart-fill"></i>
      <h5 class="m-0 fw-semibold">VetCare</h5>
    </div>

    <h4 class="fw-semibold mb-2">¿Olvidaste tu contraseña?</h4>
    <p class="text-muted small mb-4">
      No te preocupes. Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.
    </p>

    <!-- Mensaje de estado -->
    @if (session('status'))
      <div class="alert alert-success small">
        {{ session('status') }}
      </div>
    @endif

    <!-- Formulario -->
    <form method="POST" action="{{ route('password.email') }}">
      @csrf

      <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" 
               id="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-envelope me-1"></i> Enviar enlace de restablecimiento
      </button>
    </form>

    <div class="text-center mt-4">
      <a href="{{ route('login') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
      </a>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
