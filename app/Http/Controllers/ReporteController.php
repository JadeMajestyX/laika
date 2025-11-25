<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * Página principal de reportes (vista con filtros y gráficas).
     */
    public function index()
    {
        $usuario = Auth::user();
        $roles = User::query()->select('rol')->distinct()->orderBy('rol')->pluck('rol');
        return view('reportes', compact('usuario', 'roles'));
    }

    /**
     * Endpoint JSON con estadísticas y datos agregados para reportes.
     * Parámetros opcionales: from (Y-m-d), to (Y-m-d), rol
     */
    public function data(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request->input('from'), $request->input('to'));
        $rol = $request->input('rol'); // string opcional

        // Base query para citas en el rango
        $citasBase = $this->buildCitasBaseQuery($from, $to, $rol);
        $citasQuery = (clone $citasBase)->with(['servicio']);

        // Métricas básicas
        $citasAtendidas = (clone $citasQuery)->where('status', 'realizada')->count();
        $mascotasAtendidas = (clone $citasQuery)->where('status', 'realizada')->distinct('mascota_id')->count('mascota_id');

        // Clientes nuevos por ventana de tiempo
        $usuariosNuevos = User::whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count();

        // Resumen de citas por status
        $resumenCitas = (clone $citasQuery)
            ->select('status as label', DB::raw('COUNT(*) as value'))
            ->groupBy('status')
            ->get();

        $totalCitas = $resumenCitas->sum('value') ?: 1; // evitar división por cero
        $resumenCitas = $resumenCitas->map(function ($row) use ($totalCitas) {
            $row->percentage = round(($row->value / $totalCitas) * 100, 2);
            return $row;
        });

        // roles disponibles (para poblar filtro en SPA)
        $rolesDisponibles = User::query()->select('rol')->distinct()->orderBy('rol')->pluck('rol');

        return response()->json([
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'rol' => $rol,
            ],
            'roles' => $rolesDisponibles,
            'metrics' => [
                'citas_atendidas' => $citasAtendidas,
                'mascotas_atendidas' => $mascotasAtendidas,
                'usuarios_nuevos' => $usuariosNuevos,
            ],
            'resumen_citas' => $resumenCitas,
        ]);
    }

    /**
     * Exporta a CSV las citas del rango con columnas básicas.
     */
    public function exportCitas(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request->input('from'), $request->input('to'));
        $rol = $request->input('rol');

        $query = (clone $this->buildCitasBaseQuery($from, $to, $rol))
            ->with(['servicio', 'mascota'])
            ->orderBy('fecha');

        $filename = 'citas_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $out = fopen('php://output', 'w');
            // Encabezados
            fputcsv($out, ['Fecha', 'Status', 'Servicio', 'Precio', 'Mascota', 'TrabajadorID']);

            $query->chunk(500, function ($citas) use ($out) {
                foreach ($citas as $cita) {
                    fputcsv($out, [
                        optional($cita->fecha)->format('Y-m-d H:i'),
                        $cita->status,
                        optional($cita->servicio)->nombre,
                        optional($cita->servicio)->precio,
                        optional($cita->mascota)->nombre,
                        $cita->creada_por,
                    ]);
                }
            });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportación PDF con tabla formateada.
     */
    public function exportCitasPdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request->input('from'), $request->input('to'));
        $rol = $request->input('rol');

        $baseQuery = $this->buildCitasBaseQuery($from, $to, $rol);
        $citas = (clone $baseQuery)
            ->with(['servicio', 'mascota'])
            ->orderBy('fecha')
            ->get();

        $resumenCitas = (clone $baseQuery)
            ->select('status as label', DB::raw('COUNT(*) as value'))
            ->groupBy('status')
            ->get();

        $totalResumen = max(1, $resumenCitas->sum('value'));
        $palette = ['#6f42c1', '#0d6efd', '#20c997', '#fd7e14', '#ffc107', '#198754', '#dc3545'];
        $chartResumen = $resumenCitas->values()->map(function ($row, $idx) use ($totalResumen, $palette) {
            $percentage = round((($row->value ?? 0) / $totalResumen) * 100, 2);
            $percentage = max(min($percentage, 100), 0);
            $colorIndex = $idx % count($palette);
            return [
                'label' => $row->label ?? '—',
                'value' => (int) ($row->value ?? 0),
                'percentage' => $percentage,
                'width' => max($percentage, 0),
                'color' => $palette[$colorIndex],
                'color_class' => 'chart-color-' . $colorIndex,
            ];
        });

        $metrics = [
            'citas_atendidas' => (clone $baseQuery)->where('status', 'realizada')->count(),
            'mascotas_atendidas' => (clone $baseQuery)->where('status', 'realizada')->distinct('mascota_id')->count('mascota_id'),
            'usuarios_nuevos' => User::whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
        ];

        $chartMetricas = collect([
            ['label' => 'Citas atendidas', 'value' => $metrics['citas_atendidas'], 'color_idx' => 0],
            ['label' => 'Mascotas atendidas', 'value' => $metrics['mascotas_atendidas'], 'color_idx' => 2],
            ['label' => 'Usuarios nuevos', 'value' => $metrics['usuarios_nuevos'], 'color_idx' => 1],
        ]);
        $chartMetricasMax = max(1, $chartMetricas->max('value'));
        $chartMetricas = $chartMetricas->map(function ($metric) use ($chartMetricasMax, $palette) {
            $ratio = $chartMetricasMax ? round(($metric['value'] / $chartMetricasMax) * 100, 2) : 0;
            $ratio = max(min($ratio, 100), 0);
            $colorIndex = $metric['color_idx'] % count($palette);
            return array_merge($metric, [
                'ratio' => $ratio,
                'color' => $palette[$colorIndex],
                'color_class' => 'chart-color-' . $colorIndex,
            ]);
        })->values();

        $data = [
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'citas' => $citas,
            'chart_resumen' => $chartResumen,
            'chart_metricas' => $chartMetricas,
        ];

        $pdf = Pdf::loadView('reportes.export_citas_pdf', $data)->setPaper('letter', 'portrait');
        $filename = 'citas_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Resolver el rango de fechas con valores por defecto (mes actual) y validación básica.
     */
    private function resolveDateRange(?string $from, ?string $to): array
    {
        $start = $from ? Carbon::parse($from) : Carbon::now()->startOfMonth();
        $end = $to ? Carbon::parse($to) : Carbon::now()->endOfMonth();

        if ($end->lessThan($start)) {
            // si el rango es inválido, intercambiar
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    private function buildCitasBaseQuery(Carbon $from, Carbon $to, ?string $rol)
    {
        return Cita::query()
            ->when($rol, function ($q) use ($rol) {
                return $q->join('users as u_rol', 'citas.creada_por', '=', 'u_rol.id')
                    ->where('u_rol.rol', $rol);
            })
            ->whereBetween('fecha', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }
}
