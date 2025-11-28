<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Confirmación de cita</title>
</head>
<body>
    <h2>¡Tu cita ha sido confirmada!</h2>
    <p>Hola {{ $user->nombre }}{{ isset($user->apellido_paterno) ? ' ' . $user->apellido_paterno : '' }},</p>
    <p>Hemos registrado tu cita para {{ $mascota->nombre }}.</p>

    <ul>
        <li><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</li>
        <li><strong>Hora:</strong> {{ \Carbon\Carbon::parse($cita->fecha)->format('H:i') }}</li>
        @if($servicio)
            <li><strong>Servicio:</strong> {{ $servicio->nombre }}</li>
        @endif
        @if($clinica)
            <li><strong>Clínica:</strong> {{ $clinica->nombre ?? 'Clínica' }}</li>
        @endif
        <li><strong>Estado:</strong> {{ ucfirst($cita->status) }}</li>
    </ul>

    <p>Si necesitas cambiar o cancelar tu cita, por favor responde a este correo.</p>

    <p>Gracias por confiar en nosotros.</p>
</body>
</html>
