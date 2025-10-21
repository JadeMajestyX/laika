<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){

        $usuario = auth()->user();

        return view('dashboard', compact('usuario'));
    }

    public function getDashboardData()
    {
        //datos de hoy
        $hoy = Carbon::today();

        $citas = Cita::with(['mascota.user'])
            ->whereDay('fecha', $hoy->day)
            ->whereMonth('fecha', $hoy->month)
            ->whereYear('fecha', $hoy->year)
            ->orderBy('fecha', 'asc');

        $citasHoy = $citas->count();
        
        $citasCompletadas = Cita::where('status', 'completada')
                                ->whereDay('fecha', $hoy->day)
                                ->whereMonth('fecha', $hoy->month)
                                ->whereYear('fecha', $hoy->year)
                                ->count();
        $mascotasRegistradas = Mascota::whereDay('created_at', $hoy->day)
                                      ->whereMonth('created_at', $hoy->month)
                                      ->whereYear('created_at', $hoy->year)
                                      ->count();
        $clientesNuevos = User::whereDay('created_at', $hoy->day)
                              ->whereMonth('created_at', $hoy->month)
                              ->whereYear('created_at', $hoy->year)
                              ->count();

        //datos de ayer
        $ayer = Carbon::yesterday();

        $citasAyer = Cita::whereDay('fecha', $ayer->day)
                            ->whereMonth('fecha', $ayer->month)
                            ->whereYear('fecha', $ayer->year)
                            ->count();
        $citasCompletadasAyer = Cita::where('status', 'completada')
                                    ->whereDay('fecha', $ayer->day)
                                    ->whereMonth('fecha', $ayer->month)
                                    ->whereYear('fecha', $ayer->year)
                                    ->count();
        $mascotasRegistradasAyer = Mascota::whereDay('created_at', $ayer->day)
                                          ->whereMonth('created_at', $ayer->month)
                                          ->whereYear('created_at', $ayer->year)
                                          ->count();
        $clientesNuevosAyer = User::whereDay('created_at', $ayer->day)
                                  ->whereMonth('created_at', $ayer->month)
                                  ->whereYear('created_at', $ayer->year)
                                  ->count();


        $comparacionporcentaje = [
            'citasHoy' => $citasAyer > 0 ? round((($citasHoy - $citasAyer) / $citasAyer) * 100, 2) : ($citasHoy > 0 ? 100 : 0),
            'citasCompletadas' => $citasCompletadasAyer > 0 ? round((($citasCompletadas - $citasCompletadasAyer) / $citasCompletadasAyer) * 100, 2) : ($citasCompletadas > 0 ? 100 : 0),
            'mascotasRegistradas' => $mascotasRegistradasAyer > 0 ? round((($mascotasRegistradas - $mascotasRegistradasAyer) / $mascotasRegistradasAyer) * 100, 2) : ($mascotasRegistradas > 0 ? 100 : 0),
            'clientesNuevos' => $clientesNuevosAyer > 0 ? round((($clientesNuevos - $clientesNuevosAyer) / $clientesNuevosAyer) * 100, 2) : ($clientesNuevos > 0 ? 100 : 0),
        ];

        //numero de citas por dia de la semana actual
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $citasPorDia = Cita::whereBetween('fecha', [$startOfWeek, $endOfWeek])
                            ->selectRaw('DAYNAME(fecha) as dia, COUNT(*) as total')
                            ->groupBy('dia')
                            ->get();


        //obtener las ultimas 5 actividades de hoy
        $actividades = Actividad::with('user')
            ->whereDay('created_at', $hoy->day)
            ->whereMonth('created_at', $hoy->month)
            ->whereYear('created_at', $hoy->year)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($actividad) {
                return [
                    'id' => $actividad->id,
                    'accion' => $actividad->accion,
                    'modelo' => $actividad->modelo,
                    'detalles' => $actividad->detalles,
                    'user' => $actividad->user ? [
                        'id' => $actividad->user->id,
                        'nombre' => $actividad->user->nombre,
                    ] : null,
                    'created_at' => $actividad->created_at->diffForHumans(), // 💥 aquí la magia
                ];
            });


        return response()->json([
            'citasHoy' => $citasHoy,
            'citasCompletadas' => $citasCompletadas,
            'mascotasRegistradas' => $mascotasRegistradas,
            'clientesNuevos' => $clientesNuevos,

            'comparacionporcentaje' => $comparacionporcentaje,
            'citasPorDia' => $citasPorDia,
            'actividades' => $actividades,

            'citas' => $citas->get()->map(function($cita) {
                return [
                    'time' => Carbon::parse($cita->fecha)->format('h:i A'), // ej. 06:00 AM
                    'pet' => $cita->mascota->nombre ?? 'Sin nombre',
                    'owner' => $cita->mascota && $cita->mascota->user
                        ? $cita->mascota->user->nombre . ' ' . $cita->mascota->user->apellido_paterno
                        : 'Sin dueño',
                    'breed' => $cita->mascota->raza ?? '-',
                    'reason' => $cita->servicio->nombre ?? '-',
                    'clinic' => $cita->clinica->nombre ?? '-', // si tienes relación con la clínica
                    'status' => $cita->status,
                ];
            }),
        ]);

    }
}
