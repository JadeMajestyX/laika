<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GroomerDashboardController extends Controller
{
    public function index(Request $request, $any = null)
    {
        $usuario = auth()->user();
        return view('dashboard-groomer', compact('usuario'));
    }

    public function getDashboardData()
    {
        try {
            $user = Auth::user();
            $clinicaId = $user?->clinica_id;
            $today = Carbon::today();

            if (! $clinicaId) {
                Log::warning('Groomer sin clinica_id: ' . ($user->id ?? 'n/a'));
                return response()->json(['error' => 'Usuario no asociado a ninguna clínica'], 400);
            }

            // Heurística para detectar servicios de grooming por nombre (tabla `servicios` relacionada por servicio_id)
            $keywords = ['corte de pelo', 'baño', 'limpieza dental', 'peluque', 'peluquer', 'spa', 'corte de uñas', 'corte uñas', 'pelado'];
            $servicioFilter = function ($q) use ($keywords, $clinicaId) {
                $q->where('clinica_id', $clinicaId);
                $q->where(function ($q2) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $q2->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . $kw . '%']);
                    }
                });
            };

            // Métricas principales
            $citasHoy = Cita::whereDate('fecha', $today)
                ->whereHas('servicio', $servicioFilter)
                ->where('clinica_id', $clinicaId)
                ->count();

            $citasCompletadas = Cita::whereDate('fecha', $today)
                ->whereHas('servicio', $servicioFilter)
                ->where('clinica_id', $clinicaId)
                ->where('status', 'completada')
                ->count();

            $serviciosRealizados = $citasCompletadas; // para groomer, servicios completados == servicios realizados

            $mascotasAtendidas = Cita::whereDate('fecha', $today)
                ->whereHas('servicio', $servicioFilter)
                ->where('clinica_id', $clinicaId)
                ->whereIn('status', ['completada', 'en_progreso'])
                ->distinct('mascota_id')
                ->count('mascota_id');

            // Citas por día (última semana)
            $citasPorDia = [];
            for ($i = 6; $i >= 0; $i--) {
                $fecha = Carbon::today()->subDays($i);
                $dia = $fecha->locale('en')->isoFormat('dddd');
                $total = Cita::whereDate('fecha', $fecha)
                    ->whereHas('servicio', $servicioFilter)
                    ->where('clinica_id', $clinicaId)
                    ->count();
                $citasPorDia[] = ['dia' => $dia, 'total' => $total];
            }

            // Lista de próximas citas relacionadas con grooming
            $citas = Cita::with(['mascota', 'servicio', 'mascota.user'])
                ->whereHas('servicio', $servicioFilter)
                ->where('clinica_id', $clinicaId)
                ->whereDate('fecha', '>=', $today)
                ->orderBy('fecha')
                ->get()
                ->map(function($c) {
                    return [
                        'id' => $c->id,
                        'fecha' => $c->fecha?->toDateString(),
                        'hora' => $c->hora ?? ($c->fecha?->format('H:i') ?? null),
                        'mascota' => ['nombre' => $c->mascota->nombre ?? null, 'raza' => $c->mascota->raza ?? null],
                        'propietario' => $c->mascota->user->nombre ?? ($c->creador?->nombre ?? null),
                        'servicio' => ['nombre' => $c->servicio->nombre ?? null],
                        'status' => $c->status,
                    ];
                })->toArray();

            $comparacionporcentaje = [
                'citasHoy' => 0,
                'citasCompletadas' => 0,
                'serviciosRealizados' => 0,
                'mascotasAtendidas' => 0,
            ];

            $data = [
                'citasHoy' => $citasHoy,
                'citasCompletadas' => $citasCompletadas,
                'serviciosRealizados' => $serviciosRealizados,
                'mascotasAtendidas' => $mascotasAtendidas,
                'citasPorDia' => $citasPorDia,
                'actividades' => [],
                'citas' => $citas,
                'comparacionporcentaje' => $comparacionporcentaje,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error en GroomerDashboardController::getDashboardData - ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener datos del dashboard'], 500);
        }
    }
}
