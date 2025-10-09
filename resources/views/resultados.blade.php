@extends('layouts.app_admi')

@section('title', 'Resultados de búsqueda')
@section('header-title', 'Resultados de búsqueda')

@section('content')
    <h4 class="mt-4">Resultados para: <strong>{{ $query }}</strong></h4>

    <hr>

    <h5>Usuarios encontrados:</h5>
    @if($usuarios->count())
        <ul>
            @foreach($usuarios as $user)
                <li>{{ $user->nombre }} - {{ $user->email }}</li>
            @endforeach
        </ul>
    @else
        <p>No se encontraron usuarios.</p>
    @endif

    <h5 class="mt-4">Mascotas encontradas:</h5>
    @if($mascotas->count())
        <ul>
            @foreach($mascotas as $mascota)
                <li>{{ $mascota->nombre }} - {{ $mascota->raza }}</li>
            @endforeach
        </ul>
    @else
        <p>No se encontraron mascotas.</p>
    @endif
@endsection
