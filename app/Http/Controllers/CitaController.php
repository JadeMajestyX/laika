<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{
    public function index(){

    $citas = Cita::with(['clinica', 'servicio', 'mascota', 'creador'])
             ->orderBy('fecha', 'desc')
             ->paginate(10);

    $usuario = Auth::user();

        return view('citas', compact('citas', 'usuario'));
    }

    /**
     * Devuelve citas en formato JSON con paginación y filtros.
     * Parámetros:
     * - scope: 'today' (default) | 'past'
     * - page: número de página
     * - per_page: items por página (5-50)
     * - q: texto de búsqueda (clínica, servicio, mascota, propietario)
     * - from, to: rango de fechas (YYYY-MM-DD). Si se envía cualquiera, tiene prioridad sobre scope.
     */
    public function getCitasJson(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(5, min(50, $perPage));

        $scope = $request->get('scope', 'today');
        $q = trim((string) $request->get('q', ''));
        $from = $request->get('from');
        $to = $request->get('to');

        $query = Cita::with(['clinica', 'servicio', 'mascota.user', 'creador']);

        // Filtro por fecha
        $hasDateFilter = false;
        if ($from) {
            try {
                $fromDate = \Carbon\Carbon::parse($from)->startOfDay();
                $query->where('fecha', '>=', $fromDate);
                $hasDateFilter = true;
            } catch (\Exception $e) { /* ignorar formato inválido */ }
        }
        if ($to) {
            try {
                $toDate = \Carbon\Carbon::parse($to)->endOfDay();
                $query->where('fecha', '<=', $toDate);
                $hasDateFilter = true;
            } catch (\Exception $e) { /* ignorar formato inválido */ }
        }

        if (!$hasDateFilter) {
            $today = \Carbon\Carbon::today();
            if ($scope === 'past') {
                $query->whereDate('fecha', '<', $today);
            } else { // today por defecto
                $query->whereDate('fecha', '=', $today);
            }
        }

        // Búsqueda por texto
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $like = '%' . str_replace(['%','_'], ['\%','\_'], $q) . '%';
                $sub->whereHas('clinica', function ($q2) use ($like) { $q2->where('nombre', 'like', $like); })
                    ->orWhereHas('servicio', function ($q2) use ($like) { $q2->where('nombre', 'like', $like); })
                    ->orWhereHas('mascota', function ($q2) use ($like) { $q2->where('nombre', 'like', $like); })
                    ->orWhereHas('mascota.user', function ($q2) use ($like) {
                        $q2->where('nombre', 'like', $like)
                           ->orWhere('apellido_paterno', 'like', $like)
                           ->orWhere('apellido_materno', 'like', $like);
                    });
            });
        }

        $query->orderBy('fecha', 'desc');

        $paginator = $query->paginate($perPage);
        return response()->json($paginator);
    }
}
