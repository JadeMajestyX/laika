<?php

namespace App\Http\Controllers;

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

        $citasHoy = Cita::whereDay('fecha', $hoy->day)
                            ->whereMonth('fecha', $hoy->month)
                            ->whereYear('fecha', $hoy->year)
                            ->count();
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


        return response()->json([
            'citasHoy' => $citasHoy,
            'citasCompletadas' => $citasCompletadas,
            'mascotasRegistradas' => $mascotasRegistradas,
            'clientesNuevos' => $clientesNuevos,

            'comparacionporcentaje' => $comparacionporcentaje,
        ]);
    }
}
