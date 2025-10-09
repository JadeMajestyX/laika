<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $ultimoMes = Carbon::now()->subMonth()->startOfMonth();
        $users = User::all()->where('created_at', '>=', $ultimoMes);
        $citasHoy = Cita::with('mascota', 'mascota.user', 'servicio')->where('fecha', '>=', Carbon::now()->startOfDay())->get();
        $citasHoyCompletadas = Cita::with('mascota', 'mascota.user', 'servicio')->where('fecha', '>=', Carbon::now()->startOfDay())->where('status', 'completada')->get();
        $mascotasUltimoMes = User::all()->where('created_at', '>=', $ultimoMes);
        $usuario = auth()->user();

        $numero = [
            'clientesNuevosMes' => $users->count(),
            'citasHoy' => $citasHoy->count(),
            'citasHoyCompletadas' => $citasHoyCompletadas->count(),
            'mascotasUltimoMes' => $mascotasUltimoMes->count(),
        ];

        $data = [
            'citasPendientes'  => $citasHoy->where('status', '!=', 'completada')->take(5),
            'citasCompletadas' => $citasHoyCompletadas->take(5),
        ];


        return view('dashboard', compact('numero', 'data', 'usuario'));
    }
}
