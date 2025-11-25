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
    .chart-grid{ display:flex; flex-direction:column; gap:16px; margin-bottom:16px; }
    .chart-card{ border:1px solid #e0e0e0; border-radius:6px; padding:12px; }
    .chart-title{ font-size:13px; font-weight:600; margin-bottom:8px; }
    .chart-scale{ display:flex; justify-content:space-between; font-size:10px; color:#777; margin-bottom:6px; }
    .stacked-table{ width:100%; border:1px solid #e0e0e0; border-radius:9px; overflow:hidden; border-collapse:separate; border-spacing:0; margin-bottom:10px; table-layout:fixed; }
    .stacked-table td{ padding:0; height:18px; }
    .legend-table{ width:100%; border-collapse:collapse; }
    .legend-table td{ border:none; padding:4px 0; font-size:11px; }
    .legend-color{ width:12px; height:12px; display:inline-block; border-radius:2px; margin-right:6px; }
    .bar-table{ width:100%; border-collapse:collapse; }
    .bar-table td{ border:none; padding:4px 0; font-size:11px; vertical-align:middle; }
    .bar-track-table{ width:100%; border-collapse:collapse; border:1px solid #e0e0e0; border-radius:5px; overflow:hidden; table-layout:fixed; }
    .bar-track-table td{ padding:0; height:10px; }
    .metric-value{ text-align:right; font-weight:600; }
    .chart-color-0{ background:#6f42c1; }
    .chart-color-1{ background:#0d6efd; }
    .chart-color-2{ background:#20c997; }
    .chart-color-3{ background:#fd7e14; }
    .chart-color-4{ background:#ffc107; }
    .chart-color-5{ background:#198754; }
    .chart-color-6{ background:#dc3545; }
  </style>
</head>
<body>
  <div class="title">Reporte de Citas</div>
  <div class="subtitle">Rango: {{ $from }} — {{ $to }} | Total: {{ $citas->count() }}</div>

  <div class="chart-grid">
    <div class="chart-card">
      <div class="chart-title">Distribución por estado</div>
      <div class="chart-scale"><span>0%</span><span>100%</span></div>
      <table class="stacked-table">
        <tr>
          @forelse($chart_resumen as $segment)
            <td class="{{ $segment['color_class'] }}" width="{{ $segment['width'] }}%"></td>
          @empty
            <td style="background:#f8f9fa;"></td>
          @endforelse
        </tr>
      </table>
      <table class="legend-table">
        @forelse($chart_resumen as $segment)
          <tr>
            <td><span class="legend-color {{ $segment['color_class'] }}"></span>{{ $segment['label'] }}</td>
            <td style="text-align:right;">{{ $segment['value'] }} ({{ $segment['percentage'] }}%)</td>
          </tr>
        @empty
          <tr>
            <td colspan="2" style="color:#888;">Sin datos registrados en el rango.</td>
          </tr>
        @endforelse
      </table>
    </div>
    <div class="chart-card">
      <div class="chart-title">Indicadores clave</div>
      <div class="chart-scale"><span>0%</span><span>100%</span></div>
      <table class="bar-table">
        @foreach($chart_metricas as $metric)
          <tr>
            <td style="width:35%;">{{ $metric['label'] }}</td>
            <td style="width:50%;">
              <table class="bar-track-table">
                <tr>
                  <td class="{{ $metric['color_class'] }}" width="{{ $metric['ratio'] }}%"></td>
                  <td width="{{ 100 - $metric['ratio'] }}%"></td>
                </tr>
              </table>
            </td>
            <td class="metric-value" style="width:15%;">{{ $metric['value'] }}</td>
          </tr>
        @endforeach
      </table>
    </div>
  </div>

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
