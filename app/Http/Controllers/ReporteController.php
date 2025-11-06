<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

class ReporteController extends Controller
{
    /**
     * Página principal de reportes (vista con filtros y gráficas).
     */
    public function index()
    {
        $usuario = auth()->user();
        $roles = User::query()->select('rol')->distinct()->orderBy('rol')->pluck('rol');
        return view('reportes', compact('usuario', 'roles'));
    }

    /**
     * Endpoint JSON con estadísticas y datos agregados para reportes.
     * Parámetros opcionales: from (Y-m-d), to (Y-m-d), trabajador_id (creada_por)
     */
    public function data(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request->input('from'), $request->input('to'));
        $trabajadorId = $request->integer('trabajador_id');
        $rol = $request->input('rol'); // string opcional

        // Base query para citas en el rango
        $citasQuery = Cita::query()
            ->when($trabajadorId, fn($q) => $q->where('creada_por', $trabajadorId))
            ->when($rol, function ($q) use ($rol) {
                // filtrar por rol del creador de la cita
                return $q->join('users as u_rol', 'citas.creada_por', '=', 'u_rol.id')
                         ->where('u_rol.rol', $rol);
            })
            ->whereBetween('fecha', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->with(['servicio']);

        // Métricas básicas
        $citasRealizadas = (clone $citasQuery)->where('status', 'realizada')->count();
        $mascotasAtendidas = (clone $citasQuery)->where('status', 'realizada')->distinct('mascota_id')->count('mascota_id');

        // Clientes nuevos por ventana de tiempo
        $clientesNuevos = User::whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count();

        // Ingresos: suma de precio del servicio para citas realizadas (si existe)
        $ingresosTotales = (clone $citasQuery)
            ->where('status', 'realizada')
            ->join('servicios', 'citas.servicio_id', '=', 'servicios.id')
            ->sum('servicios.precio');

        // Citas por servicio
        $citasPorServicio = (clone $citasQuery)
            ->select('servicios.nombre as label', DB::raw('COUNT(citas.id) as value'))
            ->leftJoin('servicios', 'citas.servicio_id', '=', 'servicios.id')
            ->groupBy('servicios.nombre')
            ->orderByDesc('value')
            ->get();

        // Servicios con ingresos (solo realizadas)
        $serviciosTop = (clone $citasQuery)
            ->where('status', 'realizada')
            ->join('servicios', 'citas.servicio_id', '=', 'servicios.id')
            ->select(
                'servicios.nombre as label',
                DB::raw('COUNT(citas.id) as cantidad'),
                DB::raw('SUM(servicios.precio) as ingresos')
            )
            ->groupBy('servicios.nombre')
            ->orderByDesc('cantidad')
            ->limit(10)
            ->get();

        // Mascotas por especie en el rango: se toma de las mascotas involucradas en citas del rango
        $mascotasPorEspecie = Mascota::query()
            ->select('mascotas.especie as label', DB::raw('COUNT(DISTINCT mascotas.id) as value'))
            ->whereIn('mascotas.id', Cita::query()
                ->when($trabajadorId, fn($q) => $q->where('creada_por', $trabajadorId))
                ->when($rol, function ($q) use ($rol) {
                    return $q->join('users as u_rol2', 'citas.creada_por', '=', 'u_rol2.id')
                             ->where('u_rol2.rol', $rol);
                })
                ->whereBetween('fecha', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->pluck('mascota_id')
                ->filter()
            )
            ->groupBy('mascotas.especie')
            ->orderByDesc('value')
            ->get();

        // Ingresos mensuales (últimos 6 meses desde $to) como referencia
        $startMonthly = $to->copy()->subMonthsNoOverflow(5)->startOfMonth();
        $ingresosMensuales = Cita::query()
            ->when($trabajadorId, fn($q) => $q->where('creada_por', $trabajadorId))
            ->when($rol, function ($q) use ($rol) {
                return $q->join('users as u_rol3', 'citas.creada_por', '=', 'u_rol3.id')
                         ->where('u_rol3.rol', $rol);
            })
            ->whereBetween('fecha', [$startMonthly, $to->copy()->endOfMonth()])
            ->where('status', 'realizada')
            ->join('servicios', 'citas.servicio_id', '=', 'servicios.id')
            ->select(
                DB::raw("DATE_FORMAT(citas.fecha, '%Y-%m') as month"),
                DB::raw('SUM(servicios.precio) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

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
                'trabajador_id' => $trabajadorId,
                'rol' => $rol,
            ],
            'roles' => $rolesDisponibles,
            'metrics' => [
                'citas_realizadas' => $citasRealizadas,
                'mascotas_atendidas' => $mascotasAtendidas,
                'clientes_nuevos' => $clientesNuevos,
                'ingresos_totales' => (float) $ingresosTotales,
            ],
            'charts' => [
                'citas_por_servicio' => $citasPorServicio,
                'mascotas_por_especie' => $mascotasPorEspecie,
                'ingresos_mensuales' => $ingresosMensuales,
                'servicios_top' => $serviciosTop,
            ],
            'resumen_citas' => $resumenCitas,
        ]);
    }

    /**
     * Exporta a CSV las citas del rango (opcionalmente por trabajador) con columnas básicas.
     */
    public function exportCitas(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request->input('from'), $request->input('to'));
        $trabajadorId = $request->integer('trabajador_id');
        $rol = $request->input('rol');

        $query = Cita::query()
            ->with(['servicio', 'mascota'])
            ->when($trabajadorId, fn($q) => $q->where('creada_por', $trabajadorId))
            ->when($rol, function ($q) use ($rol) {
                return $q->join('users as u_rol', 'citas.creada_por', '=', 'u_rol.id')
                         ->where('u_rol.rol', $rol);
            })
            ->whereBetween('fecha', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
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
     * Exportación XLSX formateada usando Spout (streaming, memoria constante).
     */
    public function exportCitasXlsx(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request->input('from'), $request->input('to'));
        $trabajadorId = $request->integer('trabajador_id');
        $rol = $request->input('rol');

        $query = Cita::query()
            ->with(['servicio', 'mascota'])
            ->when($trabajadorId, fn($q) => $q->where('creada_por', $trabajadorId))
            ->when($rol, function ($q) use ($rol) {
                return $q->join('users as u_rol', 'citas.creada_por', '=', 'u_rol.id')
                    ->where('u_rol.rol', $rol);
            })
            ->whereBetween('fecha', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('fecha');

        $filename = 'citas_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($query) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToOutput();

            // Estilos
            $headerStyle = (new StyleBuilder())->setFontBold()->build();

            // Encabezados
            $headers = ['Fecha', 'Status', 'Servicio', 'Precio', 'Mascota', 'TrabajadorID'];
            $rowFromValues = WriterEntityFactory::createRowFromArray($headers, $headerStyle);
            $writer->addRow($rowFromValues);

            // Filas (chunk)
            $query->chunk(500, function ($citas) use ($writer) {
                foreach ($citas as $cita) {
                    $values = [
                        optional($cita->fecha)->format('Y-m-d H:i'),
                        (string) $cita->status,
                        optional($cita->servicio)->nombre,
                        optional($cita->servicio)->precio,
                        optional($cita->mascota)->nombre,
                        $cita->creada_por,
                    ];
                    $writer->addRow(WriterEntityFactory::createRowFromArray($values));
                }
            });

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Exportación PDF con tabla formateada.
     */
    public function exportCitasPdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request->input('from'), $request->input('to'));
        $trabajadorId = $request->integer('trabajador_id');
        $rol = $request->input('rol');

        $citas = Cita::query()
            ->with(['servicio', 'mascota'])
            ->when($trabajadorId, fn($q) => $q->where('creada_por', $trabajadorId))
            ->when($rol, function ($q) use ($rol) {
                return $q->join('users as u_rol', 'citas.creada_por', '=', 'u_rol.id')
                    ->where('u_rol.rol', $rol);
            })
            ->whereBetween('fecha', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('fecha')
            ->get();

        $data = [
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'citas' => $citas,
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
}
