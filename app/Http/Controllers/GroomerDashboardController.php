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

            // Buscar primero los servicios relacionados con grooming y guardar sus IDs
            $keywords = ['corte de pelo', 'baño', 'limpieza dental', 'peluque', 'peluquer', 'spa', 'corte de uñas', 'corte uñas', 'pelado'];
            $serviciosQuery = Servicio::where('clinica_id', $clinicaId)->where(function ($q2) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q2->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . $kw . '%']);
                }
            });
            $servicioIds = $serviciosQuery->pluck('id')->toArray();
            if (empty($servicioIds)) {
                // ningún servicio de grooming detectado; usar id inválido para que las consultas devuelvan vacío
                $servicioIds = [-1];
            }

            // Métricas principales
            $citasHoy = Cita::whereDate('fecha', $today)
                ->where('clinica_id', $clinicaId)
                ->whereIn('servicio_id', $servicioIds)
                ->count();

            $citasCompletadas = Cita::whereDate('fecha', $today)
                ->where('clinica_id', $clinicaId)
                ->whereIn('servicio_id', $servicioIds)
                ->where('status', 'completada')
                ->count();

            $serviciosRealizados = $citasCompletadas; // para groomer, servicios completados == servicios realizados

            $mascotasAtendidas = Cita::whereDate('fecha', $today)
                ->where('clinica_id', $clinicaId)
                ->whereIn('servicio_id', $servicioIds)
                ->whereIn('status', ['completada', 'en_progreso'])
                ->distinct('mascota_id')
                ->count('mascota_id');

            // Citas por día (última semana)
            $citasPorDia = [];
            for ($i = 6; $i >= 0; $i--) {
                $fecha = Carbon::today()->subDays($i);
                $dia = $fecha->locale('en')->isoFormat('dddd');
                $total = Cita::whereDate('fecha', $fecha)
                    ->where('clinica_id', $clinicaId)
                    ->whereIn('servicio_id', $servicioIds)
                    ->count();
                $citasPorDia[] = ['dia' => $dia, 'total' => $total];
            }

            // Lista de próximas citas relacionadas con grooming
            $citas = Cita::with(['mascota', 'servicio', 'mascota.user'])
                ->where('clinica_id', $clinicaId)
                ->whereIn('servicio_id', $servicioIds)
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


    //citas para el groomer
    public function citas(Request $request){
        try{
            $servicios = Servicio::where('clinica_id', Auth::user()->clinica_id)
                ->where(function ($q2) {
                    $keywords = ['corte de pelo', 'baño', 'limpieza dental', 'peluque', 'peluquer', 'spa', 'corte de uñas', 'corte uñas', 'pelado'];
                    foreach ($keywords as $kw) {
                        $q2->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . $kw . '%']);
                    }
                })->pluck('id')->toArray();


            $citas = Cita::with(['mascota', 'servicio', 'mascota.user'])
                ->where('clinica_id', Auth::user()->clinica_id)
                ->whereIn('servicio_id', $servicios)
                ->whereDate('fecha', '>=', Carbon::today())
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
                        'status' => $c->status ?? null,
                    ];
                })->toArray();

                return json_encode([
                    'success' => true,
                    'citas' => $citas,
                ]);
        } catch (\Exception $e){
            Log::error('Error en GroomerDashboardController::citas - ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener las citas del groomer'], 500);
        }
    }
}