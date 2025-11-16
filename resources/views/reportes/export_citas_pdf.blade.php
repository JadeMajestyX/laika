<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <style>
    body{ font-family: DejaVu Sans, sans-serif; font-size:12px; color:#333; }
    .title{ font-size:18px; font-weight:bold; margin-bottom:6px; }
    .subtitle{ font-size:12px; color:#666; margin-bottom:12px; }
    table{ width:100%; border-collapse:collapse; }
    th, td{ padding:8px; border:1px solid #e0e0e0; }
    thead th{ background:#f2f2f2; font-weight:600; }
    tfoot td{ font-weight:600; }
  </style>
</head>
<body>
  <div class="title">Reporte de Citas</div>
  <div class="subtitle">Rango: {{ $from }} — {{ $to }} | Total: {{ $citas->count() }}</div>

  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Status</th>
        <th>Servicio</th>
        <th>Precio</th>
        <th>Mascota</th>
        <th>TrabajadorID</th>
      </tr>
    </thead>
    <tbody>
      @forelse($citas as $cita)
        <tr>
          <td>{{ optional($cita->fecha)->format('Y-m-d H:i') }}</td>
          <td>{{ $cita->status }}</td>
          <td>{{ optional($cita->servicio)->nombre }}</td>
          <td>{{ optional($cita->servicio)->precio }}</td>
          <td>{{ optional($cita->mascota)->nombre }}</td>
          <td>{{ $cita->creada_por }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align:center;color:#888">Sin datos para el rango seleccionado</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
