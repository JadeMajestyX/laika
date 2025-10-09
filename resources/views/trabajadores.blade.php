@extends('layouts.app_admin')

{{-- Título de la página --}}

@section('title', 'Trabajadores')

{{-- Aside específico para esta vista --}}
@section('aside')
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('usuarios') }}"><i class="bi bi-people me-2"></i> Usuarios</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('inventario') }}"><i class="bi bi-box-seam me-2"></i> Inventario</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white active" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i> Trabajadores</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a></li>
@endsection
  @section('header-title', 'Trabajadores')

  @section('content')

      <!-- Tabla de trabajadores -->
      <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Lista de Trabajadores</span>
          <button class="btn text-white" style="background:#6f42c1; border-radius:50%; width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><i class="bi bi-plus-lg"></i></button>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Correo</th>
                <th>Edad</th>
                <th>Rol</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($trabajadores as $trabajador)
              <tr>
                <td>{{ $trabajador->nombre }}</td>
                <td>{{ $trabajador->apellido_paterno }} {{ $trabajador->apellido_materno }}</td>
                <td>{{ $trabajador->email }}</td>
                <td>{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->age }} años</td>
                <td>
                  @switch($trabajador->rol)
                    @case('A') Administrador @break
                    @case('V') Veterinario @break
                    @case('G') Groomer @break
                    @case('R') Recepcionista @break
                    @case('U') Usuario @break
                    @default Sin rol
                  @endswitch
                </td>
                <td>
                  <button class="btn btn-warning btn-sm me-1"><i class="bi bi-eye"></i></button>
                  <button class="btn btn-primary btn-sm me-1"><i class="bi bi-file-earmark-text"></i></button>
                  <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
  @endsection