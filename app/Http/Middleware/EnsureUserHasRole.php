<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $rol): Response
    {
        // Verifica que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Verifica si el usuario tiene el rol esperado
        if (!$user->hasRole($rol)) {
            // Caso específico: si intenta entrar a rutas de Admin ('A') siendo Veterinario ('V'), redirigir a su dashboard
            if ($rol === 'A' && ($user->rol ?? null) === 'V') {
                return redirect()->route('vet.dashboard');
            }

            // En otros casos, negar acceso
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }

}
