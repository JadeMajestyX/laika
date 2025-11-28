<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\User;

class GroomerDashboardController extends Controller
{
    /**
     * Renderiza la vista principal del dashboard del groomer (SPA).
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        return view('dashboard-groomer', compact('usuario'));
    }

    /**
     * GET /groomer-dashboard/data
     * Resumen para el groomer autenticado, filtrando por su clínica y grooming.
     */
    public function data(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $clinicaId = $user->clinica_id;

        $today = now()->startOfDay();
        $endToday = now()->endOfDay();

        // Detectar servicios de grooming por nombre
        $keywords = [
            'corte de pelo', 'baño', 'limpieza dental', 'spa', 'corte de uñas', 'desparasitación',
            'peluquer', 'peluque', 'baño y corte', 'limpieza', 'groom', 'aseo'
        ];
        $servicioIds = Servicio::where('clinica_id', $clinicaId)
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($kw) . '%']);
                }
            })
            ->pluck('id')
            ->all();

        // Query base: clínica + citas del groomer + servicio de grooming
        $baseQuery = Cita::query()
            ->where('clinica_id', $clinicaId)
            ->where(function ($q) use ($user) {
                $q->where('veterinario_id', $user->id)
                  ->orWhere('creada_por', $user->id);
            })
            ->when(!empty($servicioIds), function ($q) use ($servicioIds) {
                $q->whereIn('servicio_id', $servicioIds);
            });

        $citasHoy = (clone $baseQuery)
            ->whereBetween('fecha', [$today, $endToday])
            ->count();

        $citasCompletadasHoy = (clone $baseQuery)
            ->whereBetween('fecha', [$today, $endToday])
            ->where('status', 'completada')
            ->count();

        $serviciosRealizadosHoy = (clone $baseQuery)
            ->whereBetween('fecha', [$today, $endToday])
            ->whereNotNull('servicio_id')
            ->count();

        $mascotasAtendidasHoy = (clone $baseQuery)
            ->whereBetween('fecha', [$today, $endToday])
            ->whereNotNull('mascota_id')
            ->distinct('mascota_id')
            ->count('mascota_id');

        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

        $citasAyer = (clone $baseQuery)->whereBetween('fecha', [$yesterdayStart, $yesterdayEnd])->count();
        $citasCompletadasAyer = (clone $baseQuery)->whereBetween('fecha', [$yesterdayStart, $yesterdayEnd])->where('status', 'completada')->count();
        $serviciosAyer = (clone $baseQuery)->whereBetween('fecha', [$yesterdayStart, $yesterdayEnd])->whereNotNull('servicio_id')->count();
        $mascotasAyer = (clone $baseQuery)->whereBetween('fecha', [$yesterdayStart, $yesterdayEnd])->whereNotNull('mascota_id')->distinct('mascota_id')->count('mascota_id');

        $comparacion = [
            'citasHoy' => self::pctChange($citasAyer, $citasHoy),
            'citasCompletadas' => self::pctChange($citasCompletadasAyer, $citasCompletadasHoy),
            'serviciosRealizados' => self::pctChange($serviciosAyer, $serviciosRealizadosHoy),
            'mascotasAtendidas' => self::pctChange($mascotasAyer, $mascotasAtendidasHoy),
        ];

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $citasSemana = (clone $baseQuery)
            ->whereBetween('fecha', [$weekStart, $weekEnd])
            ->get(['id', 'fecha']);

        $citasPorDia = $citasSemana
            ->groupBy(fn($c) => $c->fecha?->format('l'))
            ->map(fn($grp) => ['dia' => optional($grp->first()->fecha)->format('l'), 'total' => $grp->count()])
            ->values();

        $actividades = (clone $baseQuery)
            ->whereBetween('fecha', [$today, $endToday])
            ->latest('fecha')
            ->with(['servicio:id,nombre', 'mascota:id,nombre'])
            ->take(10)
            ->get()
            ->map(function ($cita) {
                $serv = $cita->servicio?->nombre ?? 'Servicio';
                $masc = $cita->mascota?->nombre ?? 'Mascota';
                return [
                    'descripcion' => $serv.' - '.$masc,
                    'created_at' => optional($cita->fecha)->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'citasHoy' => $citasHoy,
            'citasCompletadas' => $citasCompletadasHoy,
            'serviciosRealizados' => $serviciosRealizadosHoy,
            'mascotasAtendidas' => $mascotasAtendidasHoy,
            'comparacionporcentaje' => $comparacion,
            'citasPorDia' => $citasPorDia,
            'actividades' => $actividades,
        ]);
    }

    private static function pctChange(int $prev, int $curr): int
    {
        if ($prev <= 0) return $curr > 0 ? 100 : 0;
        return (int) round((($curr - $prev) / max(1, $prev)) * 100);
    }
}