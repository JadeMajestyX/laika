<?php

namespace App\Http\Middleware;

use App\Models\Actividad;
use Closure;
use Illuminate\Http\Request;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            // Evitar duplicados: si algún proceso ya registró actividad explícitamente, no registrar de nuevo
            if ($request->attributes->get('activity_logged') === true) {
                return $response;
            }

            $user = $request->user();
            $route = $request->route();
            $action = method_exists($route, 'getActionName') ? $route->getActionName() : null;
            $name = method_exists($route, 'getName') ? $route->getName() : null;
            $path = $request->path();
            $method = $request->method();

            $modelo = $name ?: $action ?: $path;
            $modeloId = null;

            // Intentar obtener algún ID relevante de los parámetros de ruta
            if ($route && method_exists($route, 'parameters')) {
                $params = $route->parameters();
                foreach (['id', 'mascota', 'mascota_id', 'dispensador', 'dispensador_id', 'clinica_id', 'user', 'user_id'] as $key) {
                    if (isset($params[$key])) {
                        $val = $params[$key];
                        if (is_object($val) && method_exists($val, 'getKey')) {
                            $modeloId = $val->getKey();
                        } elseif (is_numeric($val)) {
                            $modeloId = (int) $val;
                        }
                        break;
                    }
                }
            }

            $input = $request->all();
            $input = $this->sanitize($input);

            Actividad::create([
                'user_id' => $user?->id,
                'accion' => $method.' '.$path,
                'modelo' => (string) $modelo,
                'modelo_id' => $modeloId,
                'detalles' => !empty($input) ? json_encode($input, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $request->ip(),
                'navegador' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // No interrumpir el flujo si falla el log
        }

        return $response;
    }

    private function sanitize($data)
    {
        $sensitive = [
            'password', 'password_confirmation', 'current_password',
            'token', 'access_token', 'authorization',
            'email_password', 'secret', 'api_key', 'otp', 'code'
        ];

        if (is_array($data)) {
            $out = [];
            foreach ($data as $k => $v) {
                $lower = strtolower((string) $k);
                if (in_array($lower, $sensitive, true)) {
                    $out[$k] = '******';
                } elseif (is_array($v)) {
                    $out[$k] = $this->sanitize($v);
                } elseif ($v instanceof \Illuminate\Http\UploadedFile) {
                    $out[$k] = 'FILE('.$v->getClientOriginalName().')';
                } else {
                    $out[$k] = $v;
                }
            }
            return $out;
        }

        return $data;
    }
}
