@extends('layouts.app_admin')

@section('title', 'Detalles de Mascota')

@section('content')
<div class="container mt-4">
    <h2>Detalles de Mascota</h2>

    <ul class="list-group mt-3">
        <li class="list-group-item"><strong>Nombre:</strong> {{ $mascota->nombre }}</li>
        <li class="list-group-item"><strong>Especie:</strong> {{ $mascota->especie }}</li>
        <li class="list-group-item"><strong>Raza:</strong> {{ $mascota->raza ?? 'No especificada' }}</li>
        <li class="list-group-item"><strong>Edad:</strong> 
            {{ $mascota->fecha_nacimiento ? \Carbon\Carbon::parse($mascota->fecha_nacimiento)->age . ' años' : 'Desconocida' }}
        </li>
        <li class="list-group-item"><strong>Peso:</strong> {{ $mascota->peso }} kg</li>
        <li class="list-group-item"><strong>Dueño:</strong> {{ $mascota->user->nombre }} {{ $mascota->user->apellido_paterno }}</li>
    </ul>

    <a href="{{ route('mascotas') }}" class="btn btn-secondary mt-3">Regresar</a>
</div>
@endsection
