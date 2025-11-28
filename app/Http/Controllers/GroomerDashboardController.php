<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\User;

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
        $citasClinica = Cita::with(['clinica:id,nombre', 'servicio:id,nombre,clinica_id', 'mascota:id,nombre,propietario_id', 'mascota.propietario:id,nombre', 'creador:id,nombre', 'veterinario:id,nombre'])
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
        ]);
    }

    private static function pctChange(int $prev, int $curr): int
    {
        if ($prev <= 0) return $curr > 0 ? 100 : 0;
        return (int) round((($curr - $prev) / max(1, $prev)) * 100);
    }
}