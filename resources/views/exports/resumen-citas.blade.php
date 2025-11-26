<!DOCTYPE html>
<html>
<head>
    <title>Resumen de Citas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .periodo {
            margin-bottom: 20px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .metricas {
            margin-bottom: 30px;
        }
        .metricas-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .metrica-item {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }
        .metrica-valor {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .metrica-label {
            color: #666;
            font-size: 14px;
        }
        .tendencia-positiva {
            color: #28a745;
        }
        .tendencia-negativa {
            color: #dc3545;
        }
        .charts {
            margin: 30px 0;
        }
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .chart-card {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }
        .chart-card h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .chart-img {
            max-width: 100%;
            height: auto;
        }
        .chart-empty {
            color: #888;
            font-style: italic;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Resumen de Citas</h1>
    </div>

    <div class="periodo">
        <strong>Periodo:</strong> {{ Carbon\Carbon::parse($periodo['desde'])->format('d/m/Y') }} - 
        {{ Carbon\Carbon::parse($periodo['hasta'])->format('d/m/Y') }}
    </div>

    <div class="metricas">
        <h2>Métricas Principales</h2>
        <div class="metricas-grid">
            <div class="metrica-item">
                <div class="metrica-valor">{{ $data['metricas']['citas'] }}</div>
                <div class="metrica-label">Citas Realizadas</div>
            </div>
            <div class="metrica-item">
                <div class="metrica-valor">{{ $data['metricas']['consultas'] }}</div>
                <div class="metrica-label">Consultas Realizadas</div>
            </div>
            <div class="metrica-item">
                <div class="metrica-valor">{{ $data['metricas']['mascotas'] }}</div>
                <div class="metrica-label">Mascotas Atendidas</div>
            </div>
        </div>
    </div>

    <div class="charts">
        <h2>Gráficas</h2>
        <div class="chart-grid">
            <div class="chart-card">
                <h3>Mascotas atendidas</h3>
                @if(!empty($data['charts']['mascotasAtendidas']))
                    <img src="{{ $data['charts']['mascotasAtendidas'] }}" alt="Gráfica de mascotas atendidas" class="chart-img">
                @else
                    <p class="chart-empty">No hay datos suficientes para esta gráfica.</p>
                @endif
            </div>
            <div class="chart-card">
                <h3>Distribución por especie</h3>
                @if(!empty($data['charts']['mascotasEspecie']))
                    <img src="{{ $data['charts']['mascotasEspecie'] }}" alt="Gráfica de especies" class="chart-img">
                @else
                    <p class="chart-empty">No hay datos suficientes para esta gráfica.</p>
                @endif
            </div>
            <div class="chart-card">
                <h3>Estados de citas</h3>
                @if(!empty($data['charts']['resumenEstados']))
                    <img src="{{ $data['charts']['resumenEstados'] }}" alt="Gráfica de estados" class="chart-img">
                @else
                    <p class="chart-empty">No hay datos suficientes para esta gráfica.</p>
                @endif
            </div>
        </div>
    </div>

    <h2>Resumen por Estado</h2>
    <table>
        <thead>
            <tr>
                <th>Estado</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
                <th>Tendencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['resumenCitas'] as $cita)
            <tr>
                <td>{{ $cita['estado'] }}</td>
                <td>{{ $cita['cantidad'] }}</td>
                <td>{{ $cita['porcentaje'] }}%</td>
                <td class="{{ $cita['tendencia'] >= 0 ? 'tendencia-positiva' : 'tendencia-negativa' }}">
                    {{ ($cita['tendencia'] >= 0 ? '+' : '') }}{{ $cita['tendencia'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>