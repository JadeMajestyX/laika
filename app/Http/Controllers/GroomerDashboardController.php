<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class GroomerDashboardController extends Controller
{
    /**
     * Renderiza la vista principal del dashboard del groomer (SPA).
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        return view('dashboard-groomer', compact('usuario'));
    }

    /**
     * GET /groomer-dashboard/data
     * Devuelve todas las citas y servicios de la clínica del groomer autenticado.
     * El filtrado por groomer se hará en el frontend.
     */
    public function data(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $clinicaId = $user->clinica_id;
        // Todas las citas de la clínica
        $citasClinica = Cita::with(['servicio:id,nombre,clinica_id', 'mascota:id,nombre', 'creador:id,nombre', 'veterinario:id,nombre'])
            ->where('clinica_id', $clinicaId)
            ->orderByDesc('fecha')
            ->get([
                'id','clinica_id','servicio_id','mascota_id','creada_por','veterinario_id','fecha','status','tipo'
            ]);

        // Todos los servicios de la clínica
        $serviciosClinica = Servicio::where('clinica_id', $clinicaId)
            ->orderBy('nombre')
            ->get(['id','clinica_id','nombre','descripcion','precio','tiempo_estimado']);

        return response()->json([
            'userId' => $user->id,
            'clinicaId' => $clinicaId,
            'citasClinica' => $citasClinica,
            'serviciosClinica' => $serviciosClinica,
            'clinicaNombre' => optional($user->clinica)->nombre ?? null,
        ]);
    }

    private static function pctChange(int $prev, int $curr): int
    {
        if ($prev <= 0) return $curr > 0 ? 100 : 0;
        return (int) round((($curr - $prev) / max(1, $prev)) * 100);
    }

    /**
     * GET /groomer-dashboard/citas/{id}
     * Devuelve detalle de una cita de la misma clínica del usuario.
     */
    public function showCita(Request $request, int $id)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $cita = Cita::with(['servicio:id,nombre,clinica_id', 'mascota:id,nombre,user_id', 'mascota.user:id,nombre', 'clinica:id,nombre'])
            ->where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->first();
        if (!$cita) return response()->json(['message' => 'Not Found'], 404);

        return response()->json($cita);
    }

    /**
     * PATCH /groomer-dashboard/citas/{id}/complete
     * Marca una cita como completada y guarda notas.
     */
    public function completeCita(Request $request, int $id)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $cita = Cita::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->first();
        if (!$cita) return response()->json(['message' => 'Not Found'], 404);

        $validated = $request->validate([
            'notas' => ['nullable', 'string'],
        ]);

        $cita->status = 'completada';
        if (array_key_exists('notas', $validated)) {
            $cita->notas = $validated['notas'];
        }
        // si no tiene veterinario asignado, asociarlo al usuario actual
        if (!$cita->veterinario_id) {
            $cita->veterinario_id = $user->id;
        }
        $cita->save();

        return response()->json(['message' => 'ok']);
    }
}