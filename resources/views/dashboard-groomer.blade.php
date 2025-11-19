@extends('layouts.app_groomer')

@section('title', 'VetCare - Panel Groomer')

@section('content')
  <div id="mainContent" data-usuario-nombre="{{ $usuario->nombre }}">
    {{-- Contenido dinámico cargado por JS --}}
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush
