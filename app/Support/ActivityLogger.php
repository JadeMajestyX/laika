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

            $effectiveUserId = $userId ?? optional($request->user())->id;
            $ip = $request->ip();
            $ua = $request->userAgent();

            // Idempotencia: evitar duplicados inmediatos de la misma acción
            $recentExists = Actividad::where('user_id', $effectiveUserId)
                ->where('accion', $accion)
                ->where('modelo', $modelo)
                ->where('modelo_id', $modeloId)
                ->where('ip_address', $ip)
                ->where('navegador', $ua)
                ->where('created_at', '>=', now()->subSeconds(30))
                ->exists();

            if ($recentExists) {
                // Marcar igualmente para que middleware no duplique
                $request->attributes->set('activity_logged', true);
                return;
            }

            Actividad::create([
                'user_id'   => $effectiveUserId,
                'accion'    => $accion,
                'modelo'    => $modelo,
                'modelo_id' => $modeloId,
                'detalles'  => $payload,
                'ip_address'=> $ip,
                'navegador' => $ua,
            ]);

            // Marcar la petición para evitar que el middleware de request logging duplique el registro
            // (el middleware debe verificar este flag antes de crear su propio registro)
            $request->attributes->set('activity_logged', true);
        } catch (\Throwable $e) {
            // Nunca romper el flujo por un fallo de logging
        }
    }
}
