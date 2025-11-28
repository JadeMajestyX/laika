<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class VetReportesController extends Controller
{
    /**
     * Endpoint principal que consume el dashboard del veterinario.
     */
    public function getReportesData(Request $request)
    {
        $data = $this->buildReportData($request);

        return response()->json($data);
    }

    /**
     * Construye todas las métricas del módulo de reportes para el veterinario autenticado.
     */
    private function buildReportData(Request $request, bool $withCharts = false): array
    {
        $veterinarioId = Auth::id();

        if (!$veterinarioId) {
            abort(401, 'Usuario no autenticado');
        }

        [$desde, $hasta, $periodo] = $this->resolveRangoFechas($request);

        if ($this->shouldSkipReportCache()) {
            return $this->buildReportPayload($desde, $hasta, $veterinarioId, $periodo, $withCharts);
        }

        $signature = $this->computeReportSignature($desde, $hasta, $veterinarioId);
        $cacheKey = $this->reportCacheKey($veterinarioId, $desde, $hasta, $withCharts, $signature);

        return Cache::remember(
            $cacheKey,
            now()->addSeconds($this->reportCacheTtl()),
            fn () => $this->buildReportPayload($desde, $hasta, $veterinarioId, $periodo, $withCharts)
        );
    }

    private function buildReportPayload(Carbon $desde, Carbon $hasta, int $veterinarioId, string $periodo, bool $withCharts): array
    {
        $data = [
            'metricas' => $this->getMetricasPrincipales($desde, $hasta, $veterinarioId),
            'mascotasAtendidas' => $this->getMascotasAtendidas($desde, $hasta, $veterinarioId),
            'mascotasEspecie' => $this->getMascotasPorEspecie($desde, $hasta, $veterinarioId),
            'resumenCitas' => $this->getResumenCitas($desde, $hasta, $veterinarioId),
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
                'filtro' => $periodo,
            ],
        ];

        if ($withCharts) {
            $data['charts'] = $this->buildCharts($data);
        }

        return $data;
    }

    /**
     * Determina el rango de fechas a partir del filtro recibido.
     */
    private function resolveRangoFechas(Request $request): array
    {
        $periodo = $request->input('periodo', 'este-mes');
        $desdeInput = $request->input('desde');
        $hastaInput = $request->input('hasta');
        $now = Carbon::now();

        switch ($periodo) {
            case 'mes-anterior':
                $desde = $now->copy()->subMonth()->startOfMonth();
                $hasta = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'trimestre-actual':
                $desde = $now->copy()->startOfQuarter();
                $hasta = $now->copy()->endOfQuarter();
                break;
            case 'personalizado':
                $desde = $desdeInput ? Carbon::parse($desdeInput) : $now->copy()->startOfMonth();
                $hasta = $hastaInput ? Carbon::parse($hastaInput) : $desde->copy()->endOfDay();
                break;
            case 'este-mes':
            default:
                $desde = $now->copy()->startOfMonth();
                $hasta = $now->copy()->endOfMonth();
                break;
        }

        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta->copy(), $desde->copy()];
        }

        return [
            $desde->copy()->startOfDay(),
            $hasta->copy()->endOfDay(),
            $periodo,
        ];
    }

    /**
     * Obtener métricas principales
     */
    private function getMetricasPrincipales(Carbon $desde, Carbon $hasta, int $veterinarioId)
    {
        $metricas = Cita::where('veterinario_id', $veterinarioId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('COUNT(*) as total_citas')
            ->selectRaw("SUM(CASE WHEN tipo = 'consulta' THEN 1 ELSE 0 END) as total_consultas")
            ->selectRaw('COUNT(DISTINCT mascota_id) as total_mascotas')
            ->first();

        if (!$metricas) {
            return [
                'citas' => 0,
                'consultas' => 0,
                'mascotas' => 0,
            ];
        }

        return [
            'citas' => (int) $metricas->total_citas,
            'consultas' => (int) $metricas->total_consultas,
            'mascotas' => (int) $metricas->total_mascotas,
        ];
    }

    /**
     * Obtener datos para la gráfica de mascotas atendidas
     */
    private function getMascotasAtendidas(Carbon $desde, Carbon $hasta, int $veterinarioId): array
    {
        $totalesPorDia = Cita::selectRaw('DATE(fecha) as dia, COUNT(DISTINCT mascota_id) as total')
            ->where('veterinario_id', $veterinarioId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('status', 'completada')
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia');

        $period = CarbonPeriod::create($desde->copy()->startOfDay(), '1 day', $hasta->copy()->startOfDay());
        $fechas = [];
        $atendidas = [];

        foreach ($period as $date) {
            $clave = $date->format('Y-m-d');
            $fechas[] = $clave;
            $atendidas[] = (int) ($totalesPorDia[$clave] ?? 0);
        }

        return [
            'fechas' => $fechas,
            'atendidas' => $atendidas,
        ];
    }

    /**
     * Obtener datos para la gráfica de mascotas por especie
     */
    private function getMascotasPorEspecie(Carbon $desde, Carbon $hasta, int $veterinarioId)
    {
        return DB::table('citas')
            ->join('mascotas', 'citas.mascota_id', '=', 'mascotas.id')
            ->whereBetween('citas.fecha', [$desde, $hasta])
            ->where('citas.status', 'completada')
            ->where('citas.veterinario_id', $veterinarioId)
            ->whereNotNull('mascotas.especie')
            ->select('mascotas.especie', DB::raw('COUNT(DISTINCT mascotas.id) as total'))
            ->groupBy('mascotas.especie')
            ->pluck('total', 'mascotas.especie')
            ->toArray();
    }

    /**
     * Obtener resumen de citas
     */
    private function getResumenCitas(Carbon $desde, Carbon $hasta, int $veterinarioId)
    {
        $rangoDias = $desde->diffInDays($hasta) + 1;
        $periodoAnterior = [
            $desde->copy()->subDays($rangoDias)->startOfDay(),
            $desde->copy()->subDay()->endOfDay(),
        ];

        $citas = Cita::where('veterinario_id', $veterinarioId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->select(DB::raw('status as estado'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('status')
            ->get();

        $total = $citas->sum('cantidad');

        $citasAnteriores = Cita::where('veterinario_id', $veterinarioId)
            ->whereBetween('fecha', $periodoAnterior)
            ->select(DB::raw('status as estado'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('status')
            ->get()
            ->pluck('cantidad', 'estado');

        return $citas->map(function ($cita) use ($total, $citasAnteriores) {
            $porcentaje = $total > 0 ? round(($cita->cantidad / $total) * 100, 2) : 0;
            $cantidadAnterior = $citasAnteriores[$cita->estado] ?? 0;
            $tendencia = $cantidadAnterior > 0
                ? round((($cita->cantidad - $cantidadAnterior) / $cantidadAnterior) * 100, 2)
                : ($cita->cantidad > 0 ? 100 : 0);

            return [
                'estado' => ucfirst($cita->estado),
                'cantidad' => $cita->cantidad,
                'porcentaje' => $porcentaje,
                'tendencia' => $tendencia,
            ];
        })->values();
    }

    private function reportCacheKey(int $veterinarioId, Carbon $desde, Carbon $hasta, bool $withCharts, string $signature): string
    {
        return sprintf(
            'vet-reportes:%d:%s:%s:%s:%d',
            $veterinarioId,
            $desde->format('YmdHis'),
            $hasta->format('YmdHis'),
            $signature,
            $withCharts ? 1 : 0
        );
    }

    private function reportCacheTtl(): int
    {
        return (int) config('cache.vet_reportes_ttl', 300);
    }

    private function shouldSkipReportCache(): bool
    {
        return app()->environment('testing') || $this->reportCacheTtl() <= 0;
    }

    private function computeReportSignature(Carbon $desde, Carbon $hasta, int $veterinarioId): string
    {
        $lastUpdated = Cita::where('veterinario_id', $veterinarioId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->max('updated_at');

        if (!$lastUpdated) {
            return 'none';
        }

        return Carbon::parse($lastUpdated)->format('YmdHis');
    }

    /**
     * Exportar resumen de citas a PDF
     */
    public function exportarPDF(Request $request)
    {
        $data = $this->buildReportData($request, true);

        $pdf = Pdf::loadView('exports.resumen-citas', [
            'data' => $data,
            'periodo' => $data['periodo'],
        ]);

        return $pdf->download('resumen-citas.pdf');
    }

    /**
     * Genera imágenes base64 para las gráficas incluidas en el PDF.
     */
    private function buildCharts(array $data): array
    {
        $charts = [];

        if (!empty($data['mascotasAtendidas']['fechas'])) {
            $charts['mascotasAtendidas'] = $this->generateChartImage([
                'type' => 'line',
                'data' => [
                    'labels' => $data['mascotasAtendidas']['fechas'],
                    'datasets' => [[
                        'label' => 'Mascotas atendidas',
                        'data' => $data['mascotasAtendidas']['atendidas'],
                        'borderColor' => '#3A7CA5',
                        'backgroundColor' => 'rgba(58,124,165,0.2)',
                        'fill' => true,
                        'tension' => 0.4,
                    ]],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => ['y' => ['beginAtZero' => true]],
                ],
            ]);
        }

        if (!empty($data['mascotasEspecie'])) {
            $charts['mascotasEspecie'] = $this->generateChartImage([
                'type' => 'doughnut',
                'data' => [
                    'labels' => array_keys($data['mascotasEspecie']),
                    'datasets' => [[
                        'data' => array_values($data['mascotasEspecie']),
                        'backgroundColor' => ['#3A7CA5', '#6CC3D5', '#F4A261', '#2A9D8F', '#E76F51', '#8ECAE6'],
                    ]],
                ],
                'options' => [
                    'plugins' => ['legend' => ['position' => 'bottom']],
                ],
            ]);
        }

        if (!empty($data['resumenCitas'])) {
            $resumen = collect($data['resumenCitas'])->map(function ($item) {
                return is_array($item) ? $item : $item?->toArray();
            })->filter()->map(function ($item) {
                return [
                    'estado' => $item['estado'] ?? 'N/A',
                    'cantidad' => (int) ($item['cantidad'] ?? 0),
                ];
            })->all();

            $charts['resumenEstados'] = $this->generateChartImage([
                'type' => 'bar',
                'data' => [
                    'labels' => array_column($resumen, 'estado'),
                    'datasets' => [[
                        'label' => 'Citas',
                        'data' => array_column($resumen, 'cantidad'),
                        'backgroundColor' => '#2A9D8F',
                    ]],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => ['y' => ['beginAtZero' => true]],
                ],
            ]);
        }

        return $charts;
    }

    private function generateChartImage(array $chartConfig, int $width = 700, int $height = 300): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withOptions(['http_errors' => false])
                ->get('https://quickchart.io/chart', [
                    'c' => json_encode($chartConfig),
                    'width' => $width,
                    'height' => $height,
                    'format' => 'png',
                    'backgroundColor' => 'white',
                ]);

            if (!$response->successful()) {
                return null;
            }

            return 'data:image/png;base64,' . base64_encode($response->body());
        } catch (\Throwable $th) {
            Log::warning('No se pudo generar la gráfica para PDF: ' . $th->getMessage());
            return null;
        }
    }
}