<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use App\Models\Receta;
use App\Models\RecetaItem;
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

            Log::info("VetCitaFichaController: Buscando cita {$id}");

            // Cargar cita con relaciones
            $cita = Cita::with(['mascota.user', 'servicio', 'receta.items'])->find($id);
            
            if (!$cita) {
                Log::warning("VetCitaFichaController: Cita {$id} no encontrada");
                return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
            }

            Log::info("VetCitaFichaController: Cita {$id} encontrada, mascota_id: {$cita->mascota_id}");

            // Opcional: validar que la cita pertenece a la misma clínica del veterinario
            if ($user->clinica_id && $cita->clinica_id && $user->clinica_id !== $cita->clinica_id) {
                return response()->json(['success' => false, 'message' => 'Cita fuera de su clínica'], 403);
            }

            // Construir datos de mascota
            $mascotaData = null;
            $mascota = null;
            
            if ($cita->mascota_id) {
                // Buscar mascota directamente en la tabla mascotas
                $mascota = \App\Models\Mascota::with('user')->find($cita->mascota_id);
                
                if ($mascota) {
                    Log::info("VetCitaFichaController: Mascota {$mascota->id} cargada: {$mascota->nombre}");
                    
                    $mascotaData = [
                        'id' => $mascota->id,
                        'nombre' => $mascota->nombre ?? 'Sin nombre',
                        'especie' => $mascota->especie ?? 'No especificada',
                        'raza' => $mascota->raza ?? 'No especificada',
                        'sexo' => $mascota->sexo ?? 'No especificado',
                        'peso' => $mascota->peso ?? null,
                        'fecha_nacimiento' => $mascota->fecha_nacimiento ? Carbon::parse($mascota->fecha_nacimiento)->format('Y-m-d') : null,
                        'imagen_url' => $mascota->imagen ? asset('uploads/mascotas/' . $mascota->imagen) : null,
                        'notas' => $mascota->notas ?? null,
                    ];

                    // Agregar datos del propietario si existe
                    if ($mascota->user) {
                        $mascotaData['propietario'] = [
                            'id' => $mascota->user->id,
                            'nombre' => $mascota->user->nombre ?? '',
                            'apellido' => $mascota->user->apellido_paterno ?? '',
                            'telefono' => $mascota->user->telefono ?? '',
                            'email' => $mascota->user->email ?? '',
                        ];
                    }
                } else {
                    Log::warning("VetCitaFichaController: Mascota {$cita->mascota_id} no encontrada en BD");
                }
            }

            // Historial: citas completadas anteriores de la misma mascota (excluyendo la actual)
            $historial = [];
            if ($mascota) {
                $historial = Cita::with('servicio')
                    ->where('mascota_id', $mascota->id)
                    ->where('id', '!=', $cita->id)
                    ->where('status', 'completada')
                    ->orderBy('fecha', 'desc')
                    ->take(25)
                    ->get()
                    ->map(function ($row) {
                        return [
                            'id' => $row->id,
                            'fecha' => Carbon::parse($row->fecha)->format('Y-m-d H:i'),
                            'servicio' => $row->servicio?->nombre ?? 'Consulta',
                            'motivo' => $row->notas ?? null,
                            'diagnostico' => $row->diagnostico ?? null,
                        ];
                    });
                
                Log::info("VetCitaFichaController: Historial de mascota {$mascota->id}: " . $historial->count() . " citas");
            }

            // Construir datos de receta
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
                    'tipo' => $cita->tipo ?? 'cita',
                    'status' => $cita->status,
                    'notas' => $cita->notas,
                    'diagnostico' => $cita->diagnostico,
                    'servicio' => $cita->servicio?->nombre ?? 'Sin servicio',
                    'veterinario_id' => $cita->veterinario_id,
                    'clinica_id' => $cita->clinica_id,
                    'mascota_id' => $cita->mascota_id,
                ],
                'mascota' => $mascotaData,
                'historial' => $historial,
                'receta' => $recetaData,
            ]);
            
        } catch (\Throwable $e) {
            Log::error('Error en VetCitaFichaController@show: ' . $e->getMessage(), [
                'cita_id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno obteniendo ficha',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'line' => config('app.debug') ? $e->getLine() : null,
            ], 500);
        }
    }

    /**
     * Actualiza el diagnóstico de una cita
     */
    public function updateDiagnostico(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $cita = Cita::find($id);
            if (!$cita) {
                return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
            }

            // Validar que pertenece a la clínica del veterinario
            if ($user->clinica_id && $cita->clinica_id && $user->clinica_id !== $cita->clinica_id) {
                return response()->json(['success' => false, 'message' => 'Sin permiso para modificar esta cita'], 403);
            }

            $validated = $request->validate([
                'diagnostico' => 'nullable|string|max:5000',
            ]);

            $cita->diagnostico = $validated['diagnostico'];
            $cita->save();

            Log::info("Diagnóstico actualizado en cita {$id} por veterinario {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Diagnóstico guardado correctamente',
                'diagnostico' => $cita->diagnostico,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error en VetCitaFichaController@updateDiagnostico: ' . $e->getMessage(), [
                'cita_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar diagnóstico',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Guarda o actualiza la receta de una cita
     */
    public function storeReceta(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $cita = Cita::with('receta.items')->find($id);
            if (!$cita) {
                return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
            }

            // Validar que pertenece a la clínica del veterinario
            if ($user->clinica_id && $cita->clinica_id && $user->clinica_id !== $cita->clinica_id) {
                return response()->json(['success' => false, 'message' => 'Sin permiso para modificar esta cita'], 403);
            }

            $validated = $request->validate([
                'diagnostico' => 'nullable|string|max:5000',
                'notas' => 'nullable|string|max:1000',
                'items' => 'nullable|array',
                'items.*.medicamento' => 'required_with:items|string|max:200',
                'items.*.dosis' => 'required_with:items|string|max:300',
                'items.*.notas' => 'nullable|string|max:300',
            ]);

            // Actualizar diagnóstico si viene
            if (array_key_exists('diagnostico', $validated)) {
                $cita->diagnostico = $validated['diagnostico'];
                $cita->save();
            }

            // Crear o actualizar receta
            $receta = $cita->receta;
            if (!$receta) {
                $receta = Receta::create([
                    'cita_id' => $cita->id,
                    'veterinario_id' => $user->id,
                    'notas' => $validated['notas'] ?? null,
                ]);
                Log::info("Nueva receta creada con ID {$receta->id} para cita {$id}");
            } else {
                if (array_key_exists('notas', $validated)) {
                    $receta->notas = $validated['notas'];
                    $receta->save();
                }
                // Limpiar items anteriores
                $itemsEliminados = $receta->items()->count();
                $receta->items()->delete();
                Log::info("Receta {$receta->id} actualizada, {$itemsEliminados} items anteriores eliminados");
            }

            // Crear nuevos items
            $itemsCreados = 0;
            if (isset($validated['items']) && count($validated['items']) > 0) {
                foreach ($validated['items'] as $item) {
                    RecetaItem::create([
                        'receta_id' => $receta->id,
                        'medicamento' => $item['medicamento'],
                        'dosis' => $item['dosis'],
                        'notas' => $item['notas'] ?? null,
                    ]);
                    $itemsCreados++;
                }
            }

            Log::info("Receta {$receta->id} guardada en cita {$id} por veterinario {$user->id}, {$itemsCreados} items creados");

            return response()->json([
                'success' => true,
                'message' => 'Receta guardada correctamente',
                'receta_id' => $receta->id,
                'items_guardados' => $itemsCreados,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error en VetCitaFichaController@storeReceta: ' . $e->getMessage(), [
                'cita_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar receta',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
