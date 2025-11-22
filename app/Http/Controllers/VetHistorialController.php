<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VetHistorialController extends Controller
{
    public function getHistorial(Request $request)
    {
        try {
            $userId = Auth::id();
            $desde = $request->query('desde');
            $hasta = $request->query('hasta');

            // Manejar correctamente las fechas con horas
            $fechaHasta = $hasta ? Carbon::createFromFormat('Y-m-d', $hasta)->endOfDay() : Carbon::today()->endOfDay();
            $fechaDesde = $desde ? Carbon::createFromFormat('Y-m-d', $desde)->startOfDay() : Carbon::today()->subDays(30)->startOfDay();

            $query = Cita::with(['mascota', 'servicio', 'mascota.user'])
                ->where('veterinario_id', $userId)
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta]) // ← Ahora incluye todo el día
                ->orderBy('fecha', 'desc');

            $perPage = (int) $request->query('por_pagina', 8);
            $page = (int) $request->query('pagina', 1);

            $paginator = $query->paginate($perPage, ['*'], 'pagina', $page);

            $actividades = $paginator->getCollection()->map(function ($cita) {
                $fecha = Carbon::parse($cita->fecha);
                
                return [
                    'id' => $cita->id,
                    'fecha' => $fecha->format('d/m/Y'),
                    'hora' => $fecha->format('H:i'),
                    'paciente' => $cita->mascota->nombre,
                    'propietario' => $cita->mascota->user->nombre ?? 'Cliente',
                    'especie' => $cita->mascota->especie,
                    'raza' => $cita->mascota->raza,
                    'tipo_actividad' => $cita->servicio->nombre,
                    'procedimiento' => $cita->notas ?? 'Consulta general',
                    'estado' => $cita->status,
                ];
            });

            //  DEBUG: Agregar logs para verificar
            \Log::info(" HISTORIAL - Desde: {$fechaDesde}, Hasta: {$fechaHasta}, Citas encontradas: {$paginator->total()}");

            return response()->json([
                'actividades' => $actividades,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en getHistorial: ' . $e->getMessage());
            return response()->json(['error' => 'Error del servidor'], 500);
        }
    }
}