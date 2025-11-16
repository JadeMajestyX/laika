<?php

namespace App\Support;

use App\Models\Actividad;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * Registra una actividad del usuario en la tabla 'actividades'.
     *
     * @param Request $request  La petición actual para capturar IP y agente de usuario
     * @param string  $accion   Descripción corta de la acción (p.ej. "Registro de usuario")
     * @param string|null $modelo   Nombre del modelo afectado (p.ej. "User", "Mascota", "Cita")
     * @param int|string|null $modeloId  ID del modelo afectado
     * @param mixed $detalles  Datos adicionales (array/objeto se serializa a JSON)
     * @param int|null $userId  ID del usuario (si no hay auth aún, se puede pasar)
     */
    public static function log(Request $request, string $accion, ?string $modelo = null, $modeloId = null, $detalles = null, ?int $userId = null): void
    {
        try {
            $payload = is_array($detalles) || is_object($detalles)
                ? json_encode($detalles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : (string) $detalles;

            Actividad::create([
                'user_id'   => $userId ?? optional($request->user())->id,
                'accion'    => $accion,
                'modelo'    => $modelo,
                'modelo_id' => $modeloId,
                'detalles'  => $payload,
                'ip_address'=> $request->ip(),
                'navegador' => $request->userAgent(),
            ]);

            // Marcar la petición para evitar que el middleware de request logging duplique el registro
            // (el middleware debe verificar este flag antes de crear su propio registro)
            $request->attributes->set('activity_logged', true);
        } catch (\Throwable $e) {
            // Nunca romper el flujo por un fallo de logging
        }
    }
}
