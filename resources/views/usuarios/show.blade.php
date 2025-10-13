@extends('layouts.app_admin')

@section('title', 'Ver usuario')

@section('header-title', 'Detalles del usuario')

@section('content')
<div class="card mt-4 shadow-sm p-4">
    <h4>{{ $cliente->nombre }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}</h4>
    <p><strong>Correo:</strong> {{ $cliente->email }}</p>
    <p><strong>Rol:</strong> {{ $cliente->rol }}</p>
    <p><strong>Edad:</strong> {{ \Carbon\Carbon::parse($cliente->fecha_nacimiento)->age }} años</p>
    <p><strong>Mascotas registradas:</strong> {{ $cliente->mascotas->count() }}</p>

    <a href="{{ route('usuarios') }}" class="btn btn-secondary mt-3">Regresar</a>
</div>
@endsection
