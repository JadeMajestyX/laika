@extends('layouts.app_admin')

{{-- Título de la página --}}

@section('title', 'Inventario')

{{-- Aside específico para esta vista --}}
@section('aside')
  <a class="nav-link text-white" href="{{route('dashboard')}}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
  </li>
  <li class="nav-item mb-2">
    <a class="nav-link text-white" href="#"><i class="bi bi-people me-2"></i> Usuarios</a>
  </li>
  <li class="nav-item mb-2">
    <a class="nav-link text-white" href="{{ route('mascotas') }}"><i class="bi bi-basket2 me-2"></i> Mascotas</a>
  </li>
  <li class="nav-item mb-2">
    <a class="nav-link text-white bg-white bg-opacity-10 rounded active" href="{{ route('inventario') }}"><i
        class="bi bi-box-seam me-2"></i> Inventario</a>
  </li>
  <li class="nav-item mb-2">
    <a class="nav-link text-white" href="{{ route('trabajadores') }}"><i class="bi bi-person-badge me-2"></i>
      Trabajadores</a>
  </li>
  <li class="nav-item mb-2">
    <a class="nav-link text-white" href="{{ route('reportes') }}"><i class="bi bi-clipboard-data me-2"></i> Reportes</a>
  </li>
  <li class="nav-item mb-2">
    <a class="nav-link text-white" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i> Configuración</a>
  </li>
  <li class="nav-item mb-2">
@endsection

  @section('header-title', 'Inventario')

  @section('content')
    <div class="card shadow-sm mt-4">
      <div class="card-header d-flex justify-content-between fw-bold">
        <span>Productos</span>
        <a href="#">Ver todos</a>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Producto</th>
              <th>Categoría</th>
              <th>Stock</th>
              <th>SKU</th>
              <th>Marca</th>
              <th>Precio</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Vacuna</td>
              <td>Antibiótico</td>
              <td>245</td>
              <td>1434</td>
              <td>X</td>
              <td>$200</td>
              <td>
                <button class="btn btn-warning btn-sm me-1"><i class="bi bi-eye"></i></button>
                <button class="btn btn-primary btn-sm me-1"><i class="bi bi-file-earmark-text"></i></button>
                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>Shampoo</td>
              <td>Shampoo</td>
              <td>123</td>
              <td>1346</td>
              <td>XY</td>
              <td>$60</td>
              <td>
                <button class="btn btn-warning btn-sm me-1"><i class="bi bi-eye"></i></button>
                <button class="btn btn-primary btn-sm me-1"><i class="bi bi-file-earmark-text"></i></button>
                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Movimientos -->
    <div class="card shadow-sm mt-4">
      <div class="card-header d-flex justify-content-between fw-bold">
        <span>Movimientos</span>
        <a href="#">Ver todos</a>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Tipo</th>
              <th>Categoría</th>
              <th>Cantidad</th>
              <th>SKU</th>
              <th>Marca</th>
              <th>Precio compra / venta</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Entrada</td>
              <td>Antibiótico</td>
              <td>50</td>
              <td>1434</td>
              <td>X</td>
              <td>$10,000</td>
            </tr>
            <tr>
              <td>Salida</td>
              <td>Shampoo</td>
              <td>2</td>
              <td>1346</td>
              <td>XY</td>
              <td>$120</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  @endsection