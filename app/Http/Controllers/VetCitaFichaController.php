<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VetCitaFichaController extends Controller
{
    /**
     * Devuelve datos completos para la ficha de una cita/consulta:
     * - cita (incluye servicio, receta e items)
     * - mascota (con propietario)
     * - historial de citas atendidas (completadas) de la misma mascota
     */
    public function show(Request $request, $id)
    {
        try {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }

        $cita = Cita::with(['mascota.user', 'servicio', 'receta.items'])->find($id);
        if (!$cita) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }

        // Opcional: validar que la cita pertenece a la misma clínica del veterinario
        if ($user->clinica_id && $cita->clinica_id && $user->clinica_id !== $cita->clinica_id) {
            return response()->json(['success' => false, 'message' => 'Cita fuera de su clínica'], 403);
        }

        $mascota = $cita->mascota;
        $mascotaData = null;
        if ($mascota) {
            $prop = $mascota->user;
            $mascotaData = [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
                'especie' => $mascota->especie,
                'raza' => $mascota->raza,
                'sexo' => $mascota->sexo,
                'peso' => $mascota->peso,
                'imagen_url' => $mascota->imagen ? asset('uploads/mascotas/' . $mascota->imagen) : null,
                'propietario' => $prop ? [
                    'id' => $prop->id,
                    'nombre' => $prop->nombre,
                    'apellido' => $prop->apellido_paterno,
                    'telefono' => $prop->telefono,
                    'email' => $prop->email,
                ] : null,
            ];
        }

        // Historial: citas completadas anteriores de la misma mascota (excluyendo la actual)
        $historial = [];
        if ($mascota) {
            $historial = Cita::where('mascota_id', $mascota->id)
                ->where('id', '!=', $cita->id)
                ->where(function ($q) {
                    $q->where('status', 'completada')->orWhere('estado', 'completada');
                })
                ->orderBy('fecha', 'desc')
                ->take(25)
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'fecha' => Carbon::parse($row->fecha)->format('Y-m-d H:i'),
                        'servicio' => $row->servicio?->nombre,
                        'motivo' => $row->notas ?? $row->motivo ?? null,
                        'diagnostico' => $row->diagnostico,
                    ];
                });
        }

        $recetaData = null;
        if ($cita->receta) {
            $recetaData = [
                'id' => $cita->receta->id,
                'notas' => $cita->receta->notas,
                'items' => $cita->receta->items->map(function ($it) {
                    return [
                        'id' => $it->id,
                        'medicamento' => $it->medicamento,
                        'dosis' => $it->dosis,
                        'notas' => $it->notas,
                    ];
                }),
            ];
        }

        return response()->json([
            'success' => true,
            'cita' => [
                'id' => $cita->id,
                'fecha' => Carbon::parse($cita->fecha)->format('Y-m-d H:i'),
                'tipo' => $cita->tipo,
                'status' => $cita->status,
                'notas' => $cita->notas,
                'diagnostico' => $cita->diagnostico,
                'servicio' => $cita->servicio?->nombre,
                'veterinario_id' => $cita->veterinario_id,
                'clinica_id' => $cita->clinica_id,
                'mascota_id' => $cita->mascota_id,
            ],
            'mascota' => $mascotaData,
            'historial' => $historial,
            'receta' => $recetaData,
        ]);
        } catch (\Throwable $e) {
            Log::error('Error en VetCitaFichaController@show: '.$e->getMessage(), [
                'cita_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno obteniendo ficha',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
