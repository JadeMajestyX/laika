@extends('layouts.app_admin')

@section('title', 'VetCare - Panel')

@section('content')

  <div class="" id="mainContent" data-usuario-nombre="{{ $usuario->nombre }}">
    {{-- Se cargará dinamicamente el contenido de cada sección --}}
  </div>
@endsection

@push('scripts')
  <!-- Chart.js desde CDN y script de la vista sin Vite -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script src="/js/views/dashboard.js"></script>
@endpush
