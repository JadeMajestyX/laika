<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VetActividadesController extends Controller
{
    public function getCitasDisponibles()
    {
        try {
            Log::info('🔍 Buscando citas disponibles...');
            
            // Obtener la fecha y hora actual
            $now = Carbon::now();
            $user = Auth::user();
            $clinicaId = $user?->clinica_id;
            if (!$clinicaId) {
                Log::warning('Usuario sin clinica_id, devolviendo lista vacía');
                return response()->json([
                    'success' => true,
                    'citas' => [],
                    'message' => 'El veterinario no tiene clínica asignada'
                ]);
            }
            
            $citas = Cita::with(['mascota', 'servicio'])
                ->whereNull('veterinario_id')
                ->whereIn('status', ['pendiente', 'confirmada'])
                ->where('clinica_id', $clinicaId)
                // Filtrar por fecha y hora: solo citas futuras
                ->where(function($query) use ($now) {
                    $query->whereDate('fecha', '>', $now->toDateString())
                          ->orWhere(function($q) use ($now) {
                              // Si es hoy, verificar que la hora sea futura
                              $q->whereDate('fecha', $now->toDateString())
                                ->whereTime('fecha', '>=', $now->toTimeString());
                          });
                })
                ->orderBy('fecha', 'asc')
                ->get();

            Log::info(" Citas disponibles encontradas (clinica {$clinicaId}): " . $citas->count());
            Log::info(" Hora actual: " . $now->format('Y-m-d H:i:s'));

            $citasDisponibles = $citas->map(function ($cita) {
                // Extraer hora de la fecha datetime
                $fecha = Carbon::parse($cita->fecha);
                
                return [
                    'id' => $cita->id,
                    'hora' => $fecha->format('H:i'), // Extraer hora del datetime
                    'fecha' => $fecha->format('d/m/Y'), // Formatear fecha
                    'mascota' => [
                        'nombre' => $cita->mascota->nombre ?? 'Mascota',
                        'raza' => $cita->mascota->raza ?? 'N/A',
                        'especie' => $cita->mascota->especie ?? 'N/A'
                    ],
                    'propietario' => $cita->mascota->user->nombre ?? 'Cliente',
                    'servicio' => [
                        'nombre' => $cita->servicio->nombre ?? 'Consulta'
                    ],
                    'status' => $cita->status,
                    'fecha_completa' => $fecha->format('Y-m-d H:i:s') // Para debug
                ];
            });

            // Log para debug
            foreach ($citasDisponibles as $cita) {
                Log::info(" Cita disponible: {$cita['fecha_completa']} - {$cita['mascota']['nombre']}");
            }

            return response()->json([
                'success' => true,
                'citas' => $citasDisponibles
            ]);

        } catch (\Exception $e) {
            Log::error(' Error en getCitasDisponibles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener citas disponibles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getActividadesHoy()
    {
        try {
            $veterinarioId = Auth::id();
            $user = Auth::user();
            $clinicaId = $user?->clinica_id;
            $today = Carbon::today();

            if (!$clinicaId) {
                Log::warning("Veterinario {$veterinarioId} sin clinica_id en getActividadesHoy");
                return response()->json([
                    'actividades' => collect([]),
                    'estadisticas' => [
                        'total' => 0,
                        'citas' => 0,
                        'consultas_manuales' => 0,
                        'canceladas' => 0,
                        'completadas' => 0
                    ],
                    'message' => 'Sin clínica asignada'
                ]);
            }

            Log::info("Buscando actividades de hoy para clínica {$clinicaId} (vet {$veterinarioId})");

            // Traer TODAS las citas/consultas de la clínica en el día, no sólo las asignadas al veterinario actual
            $queryBase = Cita::with(['mascota', 'servicio', 'mascota.user'])
                ->where('clinica_id', $clinicaId)
                ->whereDate('fecha', $today)
                ->orderBy('fecha');

            $citasCollection = $queryBase->get();

            $actividades = $citasCollection->map(function ($cita) use ($veterinarioId) {
                $fecha = Carbon::parse($cita->fecha);
                $telefono = $cita->mascota->user->telefono ?? 'No disponible';
                return [
                    'id' => $cita->id,
                    'hora' => $fecha->format('H:i'),
                    'paciente' => $cita->mascota->nombre ?? 'Mascota no encontrada',
                    'propietario' => $cita->mascota->user->nombre ?? 'Cliente',
                    'apellido' => $cita->mascota->user->apellido_paterno ?? '',
                    'telefono' => $telefono,
                    'especie' => $cita->mascota->especie ?? 'N/A',
                    'raza' => $cita->mascota->raza ?? 'N/A',
                    'tipo_actividad' => $cita->servicio->nombre ?? 'Servicio no especificado',
                    'procedimiento' => $cita->notas ?? 'Consulta general',
                    'estado' => $cita->status,
                    'tipo' => $cita->tipo ?? 'cita',
                    'asignada_a' => $cita->veterinario_id, // id del veterinario asignado (o null)
                    'es_mia' => $cita->veterinario_id === $veterinarioId, // para resaltar en frontend
                ];
            });

            // Estadísticas a nivel clínica
            $citasHoy = $citasCollection->where('tipo', 'cita')->count();
            $consultasHoy = $citasCollection->where('tipo', 'consulta')->count();
            $estadisticas = [
                'total' => $citasCollection->count(),
                'citas' => $citasHoy,
                'consultas_manuales' => $consultasHoy,
                'canceladas' => $citasCollection->where('status', 'cancelada')->count(),
                'completadas' => $citasCollection->where('status', 'completada')->count(),
                'asignadas' => $citasCollection->whereNotNull('veterinario_id')->count(),
                'libres' => $citasCollection->whereNull('veterinario_id')->count(),
                'mias' => $citasCollection->where('veterinario_id', $veterinarioId)->count(),
            ];

            Log::info(" Actividades clínica {$clinicaId} encontradas: " . $actividades->count());

            return response()->json([
                'actividades' => $actividades,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getActividadesHoy: ' . $e->getMessage());
            return response()->json(['error' => 'Error del servidor'], 500);
        }
    }

    public function asignarCita(Request $request)
    {
        try {
            $request->validate([
                'cita_id' => 'required|exists:citas,id'
            ]);

            $cita = Cita::findOrFail($request->cita_id);
            $veterinarioId = Auth::id();
            
            // Nota: Permitimos asignar citas aunque la fecha ya haya pasado.
            // Antes se bloqueaba here, pero se requiere permitir marcar
            // como atendida o completar citas históricas desde el panel.
            $now = Carbon::now();
            $fechaCita = Carbon::parse($cita->fecha);
            if ($fechaCita->lt($now)) {
                Log::info("Asignando cita pasada (fecha: {$fechaCita->format('Y-m-d H:i:s')}) al veterinario {$veterinarioId}");
            }

            // Verificar que la cita no esté ya asignada
            if ($cita->veterinario_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta cita ya ha sido asignada a otro veterinario'
                ], 400);
            }

            // Verificar que el veterinario no se asigne a sí mismo
            if ($cita->veterinario_id === $veterinarioId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes asignada esta cita'
                ], 400);
            }

            // Asignar la cita al veterinario actual
            $cita->veterinario_id = $veterinarioId;
            $cita->save();

            // Log de la actividad
            Log::info("Cita {$cita->id} asignada al veterinario {$veterinarioId}");

            return response()->json([
                'success' => true,
                'message' => 'Cita asignada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en asignarCita: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar cita: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crearConsultaManual(Request $request)
    {
        try {
            $request->validate([
                'nombre_mascota' => 'required|string|max:100',
                'nombre_cliente' => 'required|string|max:100',
                'apellido_cliente' => 'required|string|max:100',
                'telefono_cliente' => 'required|string|max:10|min:10',
                'especie' => 'required|string|max:100',
                'raza' => 'nullable|string|max:100',
                'tipo_servicio' => 'required|string|max:150',
                'procedimiento' => 'nullable|string',
                'hora' => 'required|date_format:H:i',
                'estado' => 'required|in:pendiente,en_progreso,completada'
            ]);

            $veterinarioId = Auth::id();
            $clinicaId = 1;

            //  BUSCAR USUARIO EXISTENTE POR TELÉFONO
            $user = \App\Models\User::where('telefono', $request->telefono_cliente)->first();

            if (!$user) {
                //  CREAR NUEVO USUARIO CON APELLIDO
                $emailUnico = strtolower(str_replace(' ', '', $request->nombre_cliente)) . '_' . time() . '@consultas.com';
                
                $user = \App\Models\User::create([
                    'nombre' => $request->nombre_cliente,
                    'apellido_paterno' => $request->apellido_cliente, 
                    'apellido_materno' => '', 
                    'rol' => 'U',
                    'fecha_nacimiento' => '2000-01-01',
                    'genero' => 'O',
                    'email' => $emailUnico,
                    'telefono' => $request->telefono_cliente,
                    'password' => bcrypt(uniqid()),
                ]);
            }

            // Buscar o crear el servicio "Consulta Manual"
            // $servicio = \App\Models\Servicio::firstOrCreate(
            //     ['nombre' => 'Consulta', 'clinica_id' => $clinicaId],
            //     [
            //         'descripcion' => 'Consulta manual creada por veterinario',
            //         'precio' => 0.00,
            //         'tiempo_estimado' => 30
            //     ]
            // );

            // CREAR MASCOTA para el usuario
            $mascota = \App\Models\Mascota::create([
                'user_id' => $user->id,
                'nombre' => $request->nombre_mascota,
                'especie' => $request->especie,
                'raza' => $request->raza ?? 'No especificada',
                'sexo' => 'M',
                'peso' => 0.00,
                'notas' => 'Creada para consulta manual'
            ]);

            $fechaCompleta = Carbon::today()->setTimeFromTimeString($request->hora);

            // Crear la consulta manual
            $cita = \App\Models\Cita::create([
                'clinica_id' => $clinicaId,
                'servicio_id' => $servicio->id,
                'mascota_id' => $mascota->id,
                'creada_por' => $veterinarioId,
                'veterinario_id' => $veterinarioId,
                'fecha' => $fechaCompleta,
                'notas' => $request->procedimiento ?? 'Consulta manual',
                'status' => $request->estado,
                'tipo' => 'consulta'
            ]);

            Log::info(" Consulta creada - Cliente: {$request->nombre_cliente} {$request->apellido_cliente}");

            return response()->json([
                'success' => true,
                'message' => 'Consulta manual creada correctamente',
                'cita_id' => $cita->id
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al crear consulta manual: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear consulta manual: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarEstadoActividad(Request $request)
    {
        try {
            $request->validate([
                'cita_id' => 'required|exists:citas,id',
                'estado' => 'required|in:pendiente,confirmada,en_progreso,completada,cancelada'
            ]);

            Log::info(" Actualizando estado de cita {$request->cita_id} a {$request->estado}");

            // Buscar la cita por id
            $cita = Cita::find($request->cita_id);
            if (!$cita) {
                Log::warning("Cita {$request->cita_id} no encontrada al intentar actualizar estado");
                return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
            }

            $user = Auth::user();
            $veterinarioId = Auth::id();

            // Permitir actualizar si:
            // - el veterinario está asignado a la cita, o
            // - el veterinario pertenece a la misma clínica que la cita (permiso local)
            $puedeActualizar = false;
            if ($cita->veterinario_id !== null && $cita->veterinario_id == $veterinarioId) {
                $puedeActualizar = true;
            } elseif ($cita->clinica_id && $user && $user->clinica_id && $cita->clinica_id == $user->clinica_id) {
                // Permitir a cualquier trabajador de la misma clínica actualizar estados (útil para citas no asignadas o pasadas)
                $puedeActualizar = true;
            }

            if (! $puedeActualizar) {
                Log::warning("Usuario {$veterinarioId} no autorizado para actualizar la cita {$cita->id}");
                return response()->json(['success' => false, 'message' => 'No autorizado para actualizar esta cita'], 403);
            }

            $estadoAnterior = $cita->status;
            $cita->status = $request->estado;
            $cita->save();

            // Log de la actividad
            Log::info(" Estado de cita {$cita->id} cambiado de {$estadoAnterior} a {$request->estado} por veterinario " . Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $request->estado
            ]);

        } catch (\Exception $e) {
            Log::error(' Error en actualizarEstadoActividad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtener detalle de cita y historial
    public function getCitaDetalle($citaId)
    {
        try {
            $veterinarioId = Auth::id();
            
            // Obtener información de la cita actual
            $cita = Cita::with(['mascota', 'mascota.user', 'servicio'])
                ->where('id', $citaId)
                ->where('veterinario_id', $veterinarioId)
                ->firstOrFail();
            
            // Obtener historial de la mascota (excluyendo la cita actual)
            $historial = Cita::with(['servicio'])
                ->where('mascota_id', $cita->mascota_id)
                ->where('id', '!=', $citaId)
                ->whereDate('fecha', '<', $cita->fecha)
                ->orderBy('fecha', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($histCita) {
                    return [
                        'fecha' => \Carbon\Carbon::parse($histCita->fecha)->format('d/m/Y H:i'),
                        'tipo_actividad' => $histCita->servicio->nombre,
                        'estado' => $histCita->status,
                        'procedimiento' => $histCita->notas
                    ];
                });
            
            return response()->json([
                'success' => true,
                'cita' => [
                    'id' => $cita->id,
                    'mascota_nombre' => $cita->mascota->nombre,
                    'mascota_especie' => $cita->mascota->especie,
                    'mascota_raza' => $cita->mascota->raza,
                    'propietario_nombre' => $cita->mascota->user->nombre,
                    'propietario_telefono' => $cita->mascota->user->telefono,
                    'notas' => $cita->notas,
                    'tipo_actividad' => $cita->servicio->nombre
                ],
                'historial' => $historial
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getCitaDetalle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle de la cita'
            ], 500);
        }
    }

// Finalizar cita
    public function finalizarCita(Request $request)
    {
        try {
            $request->validate([
                'cita_id' => 'required|exists:citas,id',
                'procedimiento' => 'nullable|string'
            ]);

            $cita = Cita::where('id', $request->cita_id)
                ->where('veterinario_id', Auth::id())
                ->firstOrFail();

            $cita->status = 'completada';
            $cita->notas = $request->procedimiento ?? $cita->notas;
            $cita->save();

            Log::info(" Cita {$cita->id} finalizada por veterinario " . Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Cita finalizada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en finalizarCita: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la cita'
            ], 500);
        }
    }
}