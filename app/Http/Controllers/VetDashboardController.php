<?php
// app/Http/Controllers/VetDashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Actividad;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VetDashboardController extends Controller
{
    public function index(Request $request, $any = null)
    {
        $usuario = auth()->user();
        return view('dashboard-vet', compact('usuario'));
    }

    public function getDashboardData()
    {
        try {
            $today = Carbon::today();
            $veterinarioId = Auth::id();

            // CITAS DE HOY - solo tipo 'cita' 
            $citasHoy = Cita::where('veterinario_id', $veterinarioId)
                ->whereDate('fecha', $today)
                ->where('tipo', 'cita') 
                ->count();
                
            // CITAS COMPLETADAS - solo tipo 'cita'
            $citasCompletadas = Cita::where('veterinario_id', $veterinarioId)
                ->whereDate('fecha', $today)
                ->where('status', 'completada')
                ->where('tipo', 'cita') 
                ->count();
                
            // CONSULTAS REALIZADAS - solo tipo 'consulta'
            $consultasRealizadas = Cita::where('veterinario_id', $veterinarioId)
                ->whereDate('fecha', $today)
                ->where('tipo', 'consulta')
                ->where('status', 'completada') // ← SOLO CONSULTAS
                ->count();
                
            // MASCOTAS ATENDIDAS - ambos tipos pero solo status completado/en progreso
            $mascotasAtendidas = Cita::where('veterinario_id', $veterinarioId)
                ->whereDate('fecha', $today)
                ->whereIn('status', ['completada', 'en_progreso'])
                ->distinct('mascota_id')
                ->count('mascota_id');
            
            // CITAS POR DÍA - solo tipo 'cita' para el gráfico
            $citasPorDia = [];
            for ($i = 6; $i >= 0; $i--) {
                $fecha = Carbon::today()->subDays($i);
                $citasPorDia[] = [
                    'dia' => $fecha->format('l'), // Lunes, Martes, etc.
                    'total' => Cita::where('veterinario_id', $veterinarioId)
                        ->whereDate('fecha', $fecha)
                        ->where('tipo', 'cita') // ← SOLO CITAS
                        ->count()
                ];
            }

            // PRÓXIMAS CITAS DISPONIBLES - solo tipo 'cita' y no asignadas
            $actividades = Cita::with(['mascota', 'servicio', 'mascota.user'])
                ->whereNull('veterinario_id')
                ->whereDate('fecha', '>=', $today)
                ->whereIn('status', ['pendiente', 'confirmada'])
                ->where('tipo', 'cita') // ← SOLO CITAS REALES PARA ASIGNAR
                ->orderBy('fecha')
                ->take(5)
                ->get()
                ->map(function ($cita) {
                    return [
                        'id' => $cita->id,
                        'mascota_nombre' => $cita->mascota->nombre,
                        'user_nombre' => $cita->mascota->user->nombre ?? 'Cliente',
                        'fecha' => $cita->fecha,
                        'hora' => $cita->hora,
                        'tipo' => $cita->servicio->nombre,
                        'especie' => $cita->mascota->especie
                    ];
                });

            // COMPARACIÓN CON EL DÍA ANTERIOR
            $ayer = Carbon::yesterday();
            
            // CITAS DE AYER - solo tipo 'cita'
            $citasHoyAyer = Cita::where('veterinario_id', $veterinarioId)
                ->whereDate('fecha', $ayer)
                ->where('tipo', 'cita') 
                ->count();
                
            // CITAS COMPLETADAS AYER - solo tipo 'cita'
            $citasCompletadasAyer = Cita::where('veterinario_id', $veterinarioId)
                ->whereDate('fecha', $ayer)
                ->where('status', 'completada')
                ->where('tipo', 'cita') 
                ->count();
                
            // CONSULTAS REALIZADAS AYER - solo tipo 'consulta_manual'
           $consultasRealizadasAyer = Cita::where('veterinario_id', $veterinarioId)
                ->whereDate('fecha', $ayer)
                ->where('tipo', 'consulta')
                ->where('status', 'completada')
                ->count();
                
            // MASCOTAS ATENDIDAS AYER - ambos tipos
            $mascotasAtendidasAyer = Cita::where('veterinario_id', $veterinarioId)
                ->whereDate('fecha', $ayer)
                ->whereIn('status', ['completada', 'en_progreso'])
                ->distinct('mascota_id')
                ->count('mascota_id');

            // CALCULAR PORCENTAJES
            $comparacionporcentaje = [
                'citasHoy' => $this->calcularPorcentaje($citasHoy, $citasHoyAyer),
                'citasCompletadas' => $this->calcularPorcentaje($citasCompletadas, $citasCompletadasAyer),
                'consultasRealizadas' => $this->calcularPorcentaje($consultasRealizadas, $consultasRealizadasAyer),
                'mascotasAtendidas' => $this->calcularPorcentaje($mascotasAtendidas, $mascotasAtendidasAyer)
            ];

            $data = [
                'citasHoy' => $citasHoy,
                'citasCompletadas' => $citasCompletadas,
                'consultasRealizadas' => $consultasRealizadas, 
                'mascotasAtendidas' => $mascotasAtendidas,
                'citasPorDia' => $citasPorDia,
                'actividades' => $actividades,
                'comparacionporcentaje' => $comparacionporcentaje
            ];

            // LOG PARA DEBUG
           \Log::info(" Dashboard - Citas: {$citasHoy}, Citas Completadas: {$citasCompletadas}, Consultas Completadas: {$consultasRealizadas}");

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Error en getDashboardData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener datos del dashboard',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular porcentaje de cambio
     */
    private function calcularPorcentaje($valorHoy, $valorAyer)
    {
        if ($valorAyer > 0) {
            return round((($valorHoy - $valorAyer) / $valorAyer) * 100, 2);
        } elseif ($valorHoy > 0) {
            return 100; // Incremento del 100% si no había datos ayer
        } else {
            return 0; // Sin cambio si ambos son 0
        }
    }
}