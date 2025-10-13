<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet care - Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    width: 100%;
    display: flex;
    justify-content: center;
    padding: 20px;
}

.login-card {
    width: 350px;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.header {
    background-color: #7b2fff;
    color: white;
    text-align: center;
    padding: 30px 20px 45px;
    position: relative;
    border-bottom-left-radius: 50% 5px;
    border-bottom-right-radius: 50% 20px;
}

.header::after {
    content: "";
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 100%;
    height: 30px;
    background-color: white;
    border-top-right-radius: 100% 20px;
    border-top-left-radius: 50% 20px;
}

.circle {
    background: #eee;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 3px solid white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.header h3 {
    font-size: 24px;
    font-weight: 500;
    letter-spacing: 1px;
}

.content {
    padding: 20px 25px 30px;
}

.content h4 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
    font-weight: 500;
}

.input-group {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 6px;
    margin-bottom: 15px;
    overflow: hidden;
    background-color: #fff;
}

.input-icon {
    background-color: #7B2CF2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 100%;
    padding: 11px;
}

.input-icon img {
    width: 22px;
    height: 22px;
}

.input-group input {
    border: none;
    outline: none;
    width: 100%;
    font-size: 14px;
    color: #333;
    padding: 10px;
}

.input-group input::placeholder {
    color: #999;
}

.forgot {
    text-align: right;
    margin-bottom: 15px;
}

.forgot a {
    color: #333;
    text-decoration: none;
    font-size: 12px;
}

.forgot a:hover {
    text-decoration: underline;
}

.btn {
    width: 100%;
    border: none;
    padding: 10px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    margin-bottom: 10px;
    transition: 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn.primary {
    background-color: #7B2CF2;
    color: white;
}

.btn.primary:hover {
    background-color: #6925d9;
}
.error-message {
    display: flex;
    align-items: center;
    background-color: #ffe5e5; /* fondo rojo claro */
    color: #d8000c; /* texto rojo */
    border: 1px solid #d8000c;
    border-radius: 6px;
    padding: 10px 15px;
    margin-bottom: 15px;
    font-size: 14px;
    gap: 10px;
}

.error-message .error-icon {
    width: 20px;
    height: 20px;
}

@media (max-width: 768px) {
    .login-card {
        width: 85%;
    }
    .circle {
        width: 70px;
        height: 70px;
    }
    .content {
        padding: 15px 20px;
    }
    .btn {
        font-size: 13px;
        padding: 9px;
    }
}

@media (max-width: 480px) {
    body {
        background-color: #ffffff;
        height: auto;
    }
    .login-card {
        width: 100%;
        border-radius: 0;
        box-shadow: none;
    }
    .header {
        padding: 25px 10px 40px;
    }
    .content {
        padding: 20px 15px;
    }
    .circle {
        width: 60px;
        height: 60px;
    }
    .divider::before, .divider::after {
        width: 30%;
    }
}
</style>
<body>
    <div class="container">
        <div class="login-card">
            <div class="header">
                <div class="circle">
                    <img src="{{ asset('images/logopetcare.png') }}" alt="Logo">
                </div>
                <h3>Laika</h3>
            </div>

            <div class="content">
                <h4>Welcome</h4>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="input-group">
                        <div class="input-icon">
                            <img src="{{ asset('images/gmail.png') }}" alt="icon email">
                        </div>
                        <input type="email" name="email" placeholder="Email address:" required>
                    </div>

                    <div class="input-group">
                        <div class="input-icon">
                            <img src="{{ asset("images/password.png") }}" alt="icon password">
                        </div>
                        <input type="password" name="password" placeholder="Password:" required>
                    </div>
                    {{-- Credenciales incorrectas --}}
                    @if ($errors->any())
                        <div class="error-message">
                            <img src="../drawable/warning.png" alt="Warning" class="error-icon">
                            <span>Email o contraseña incorrectos</span>
                        </div>
                    @endif

                    <div class="forgot">
                        <a href="#">¿Forgot the password?</a>
                    </div>

                    <button type="submit" class="btn primary">Sign In</button>
                </form>

            </div>
        </div>
    </div>
</body>
</html>