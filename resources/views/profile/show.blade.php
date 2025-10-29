<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <!-- Agrega aquí tus hojas de estilo si son necesarias -->
</head>
<body>

<div class="card mt-4 shadow-sm p-4">
    <h3 class="text-lg font-semibold mb-2">{{ $user->name ?? 'Usuario' }}</h3>
    <p><strong>Correo:</strong> {{ $user->correo }}</p>

    <div class="mt-4">
        <a href="#" class="btn btn-primary">Editar perfil</a>

        <form action="{{ route('profile.destroy') }}" method="POST" class="inline-block ml-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('¿Seguro que quieres eliminar tu cuenta?')">
                Eliminar cuenta
            </button>
        </form>
    </div>
</div>

</body>
</html>
