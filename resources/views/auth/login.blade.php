<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar Sesión - VetCare</title>

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
    .login-card {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
      padding: 2.5rem;
      width: 100%;
      max-width: 420px;
      position: relative;
    }
    .login-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .6rem;
      color: var(--brand);
      margin-bottom: 1.8rem;
    }
    .login-logo i {
      background: var(--brand);
      color: #fff;
      padding: .6rem;
      border-radius: .75rem;
      font-size: 1.3rem;
    }
    h4 {
      font-weight: 600;
      text-align: center;
      margin-bottom: 1rem;
      color: #212529;
    }
    .form-label {
      font-weight: 500;
    }
    .form-control {
      border-radius: 0.5rem;
      padding: 0.65rem 0.9rem;
    }
    .btn-primary {
      background: var(--brand);
      border: none;
      font-weight: 500;
      padding: .65rem;
    }
    .btn-primary:hover {
      background: var(--brand-dark);
    }
    .text-muted a {
      color: var(--brand);
      text-decoration: none;
      font-weight: 500;
    }
    .text-muted a:hover {
      text-decoration: underline;
    }
    .alert-danger {
      font-size: .9rem;
      border-radius: .5rem;
    }
    .form-check-label {
      font-size: .9rem;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-logo">
      <i class="bi bi-heart-fill"></i>
      <h5 class="m-0 fw-semibold">VetCare</h5>
    </div>

    <h4>Iniciar sesión</h4>
    <p class="text-muted text-center small mb-4">Accede al panel de administración</p>

    @if ($errors->any())
      <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>Email o contraseña incorrectos</div>
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" required autofocus>
        @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
               required>
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="remember" id="remember">
          <label class="form-check-label" for="remember">Recuérdame</label>
        </div>
        <a href="{{ route('password.request') }}" class="small text-decoration-none text-muted">
          ¿Olvidaste tu contraseña?
        </a>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
      </button>
    </form>

    <div class="text-center mt-4 small text-muted">
      ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
