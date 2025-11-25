<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Mascota;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CitasResumenExport;

class VetReportesController extends Controller
{
    /**
     * Obtener datos para el reporte según el filtro
     */
    public function getData(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $periodo = $request->input('periodo', 'este-mes');

        // Determinar fechas según el periodo
        switch ($periodo) {
            case 'este-mes':
                $desde = Carbon::now()->startOfMonth();
                $hasta = Carbon::now()->endOfMonth();
                break;
            case 'mes-anterior':
                $desde = Carbon::now()->subMonth()->startOfMonth();
                $hasta = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'trimestre-actual':
                $desde = Carbon::now()->startOfQuarter();
                $hasta = Carbon::now()->endOfQuarter();
                break;
            case 'personalizado':
                $desde = Carbon::parse($desde);
                $hasta = Carbon::parse($hasta);
                break;
        }

        // Obtener datos de métricas principales
        $metricas = $this->getMetricasPrincipales($desde, $hasta);

        // Obtener datos para la gráfica de mascotas atendidas
        $mascotasAtendidas = $this->getMascotasAtendidas($desde, $hasta);

        // Obtener datos para la gráfica de mascotas por especie
        $mascotasEspecie = $this->getMascotasPorEspecie($desde, $hasta);

        // Obtener resumen de citas
        $resumenCitas = $this->getResumenCitas($desde, $hasta);

        return response()->json([
            'metricas' => $metricas,
            'mascotasAtendidas' => $mascotasAtendidas,
            'mascotasEspecie' => $mascotasEspecie,
            'resumenCitas' => $resumenCitas,
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Obtener métricas principales
     */
    private function getMetricasPrincipales($desde, $hasta)
    {
        return [
            'citas' => Cita::whereBetween('fecha', [$desde, $hasta])->count(),
            // Las "consultas" se determinan por el servicio asociado (nombre = 'consulta')
            'consultas' => Cita::whereBetween('fecha', [$desde, $hasta])
                ->whereHas('servicio', function ($q) {
                    $q->where('nombre', 'consulta');
                })
                ->count(),
            'mascotas' => Cita::whereBetween('fecha', [$desde, $hasta])
                ->distinct('mascota_id')
                ->count('mascota_id')
        ];
    }

    /**
     * Obtener datos para la gráfica de mascotas atendidas
     */
    private function getMascotasAtendidas($desde, $hasta)
    {
        $period = CarbonPeriod::create($desde, '1 day', $hasta);
        $fechas = [];
        $atendidas = [];

        foreach ($period as $date) {
            $fechas[] = $date->format('Y-m-d');
            $count = Cita::whereDate('fecha', $date)
                ->where('status', 'completada')
                ->distinct('mascota_id')
                ->count('mascota_id');
            $atendidas[] = $count;
        }

        return [
            'fechas' => $fechas,
            'atendidas' => $atendidas
        ];
    }

    /**
     * Obtener datos para la gráfica de mascotas por especie
     */
    private function getMascotasPorEspecie($desde, $hasta)
    {
        return DB::table('citas')
            ->join('mascotas', 'citas.mascota_id', '=', 'mascotas.id')
            ->whereBetween('citas.fecha', [$desde, $hasta])
            ->where('citas.status', 'completada')
            ->select('mascotas.especie', DB::raw('COUNT(DISTINCT mascotas.id) as total'))
            ->groupBy('mascotas.especie')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->especie => $item->total];
            });
    }

    /**
     * Obtener resumen de citas
     */
    private function getResumenCitas($desde, $hasta)
    {
        $total = Cita::whereBetween('fecha', [$desde, $hasta])->count();
        // Calcular periodo anterior con la misma longitud inmediatamente antes de $desde
        // Usamos diffInDays + 1 para contar inclusive los días
        $rangoDias = $desde->diffInDays($hasta) + 1;
        $periodoAnterior = [
            $desde->copy()->subDays($rangoDias),
            $desde->copy()->subDay()
        ];

        // Agrupar por la columna real `status`, pero exponerla como `estado` en los resultados
        $citas = Cita::whereBetween('fecha', [$desde, $hasta])
            ->select(DB::raw('status as estado'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('status')
            ->get();

        $citasAnteriores = Cita::whereBetween('fecha', $periodoAnterior)
            ->select(DB::raw('status as estado'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('status')
            ->get()
            ->pluck('cantidad', 'estado');

        return $citas->map(function ($cita) use ($total, $citasAnteriores) {
            $porcentaje = ($total > 0) ? round(($cita->cantidad / $total) * 100, 2) : 0;
            $cantidadAnterior = $citasAnteriores[$cita->estado] ?? 0;
            $tendencia = $cantidadAnterior > 0 
                ? round((($cita->cantidad - $cantidadAnterior) / $cantidadAnterior) * 100, 2)
                : 100;

            return [
                'estado' => ucfirst($cita->estado),
                'cantidad' => $cita->cantidad,
                'porcentaje' => $porcentaje,
                'tendencia' => $tendencia
            ];
        });
    }

    /**
     * Exportar resumen de citas a PDF
     */
    public function exportarPDF(Request $request)
    {
        $data = $this->getData($request)->original;
        
        $pdf = PDF::loadView('exports.resumen-citas', [
            'data' => $data,
            'periodo' => $data['periodo']
        ]);
        
        return $pdf->download('resumen-citas.pdf');
    }
}