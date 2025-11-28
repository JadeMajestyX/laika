@extends('layouts.app_groomer')

@section('title', 'VetCare - Panel Groomer')

@section('content')
  <div id="mainContent" data-usuario-nombre="{{ $usuario->nombre }}">
    {{-- Contenido dinámico cargado por JS --}}
  </div>
  <!-- Modal Detalle de Cita -->
  <div class="modal fade" id="citaModal" tabindex="-1" aria-labelledby="citaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="citaModalLabel">Detalle de Cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2"><span class="text-body-secondary small">Servicio:</span> <div id="citaModalServicio" class="fw-semibold"></div></div>
          <div class="mb-2"><span class="text-body-secondary small">Mascota:</span> <div id="citaModalMascota"></div></div>
          <div class="mb-2"><span class="text-body-secondary small">Propietario:</span> <div id="citaModalPropietario"></div></div>
          <div class="mb-2"><span class="text-body-secondary small">Fecha y hora:</span> <div id="citaModalFecha"></div></div>
          <div class="mb-3"><span class="text-body-secondary small">Estado:</span> <div id="citaModalEstado" class="badge bg-secondary"></div></div>
          <div class="mb-3">
            <label for="citaModalNotas" class="form-label">Notas</label>
            <textarea id="citaModalNotas" class="form-control" rows="3" placeholder="Escribe notas o comentarios..."></textarea>
          </div>
          <div id="citaModalAlert" class="alert alert-danger d-none" role="alert"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" id="citaModalCompletar" class="btn btn-primary">Marcar como completada</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush
