<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use Illuminate\Support\Facades\Auth;
use App\Services\FcmV1Client;
use App\Support\ActivityLogger;
use Carbon\Carbon;

class CitaController extends Controller
{
    public function index(){

        return view('agendarCita');
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

    /**
     * Envía recordatorio de cita a todos los usuarios con citas para hoy.
     * Solo debe ser invocado por administradores (middleware ya protege la ruta).
     */
    public function enviarRecordatorioHoy(Request $request)
    {
        $hoy = Carbon::today();
        $citasHoy = Cita::with(['mascota.user','servicio'])
            ->whereDate('fecha', $hoy)
            ->get();

        $usuariosCitas = [];
        foreach ($citasHoy as $cita) {
            $userId = $cita->mascota?->user?->id;
            if ($userId) {
                $usuariosCitas[$userId][] = $cita; // agrupar las citas por usuario
            }
        }

        $totalUsuarios = count($usuariosCitas);
        $enviados = 0;
        $fallos = 0;
        $detalles = [];

        if (config('fcm.use_v1') && $totalUsuarios > 0) {
            $client = new FcmV1Client();
            foreach ($usuariosCitas as $userId => $citasDelUsuario) {
                $primera = $citasDelUsuario[0];
                $mascotaNombre = $primera->mascota?->nombre ?? 'tu mascota';
                $total = count($citasDelUsuario);
                $title = 'Recordatorio de cita';
                $body = $total > 1
                    ? "Tienes $total citas programadas para hoy. No olvides asistir."
                    : "Tienes una cita hoy para $mascotaNombre. ¡Te esperamos!";
                $data = [
                    'tipo' => 'cita_recordatorio',
                    'fecha' => (string)$hoy->toDateString(),
                    'total_citas' => (string)$total,
                ];
                try {
                    $summary = $client->sendToUser($userId, $title, $body, $data);
                    $enviados += $summary['success'];
                    $fallos += $summary['fail'];
                    $detalles[$userId] = $summary;
                    ActivityLogger::log($request, 'Enviar recordatorio cita hoy', 'User', $userId, [
                        'total_citas' => $total,
                        'success' => $summary['success'],
                        'fail' => $summary['fail'],
                    ], Auth::id());
                } catch (\Throwable $e) {
                    $fallos++;
                    $detalles[$userId] = ['error' => $e->getMessage()];
                }
            }
        }

        // Si se envía vía fetch JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'usuarios_notificados' => $totalUsuarios,
                'envios_exitosos' => $enviados,
                'envios_fallidos' => $fallos,
                'detalles' => $detalles,
            ]);
        }

        return redirect()->back()->with('recordatorio_result', [
            'usuarios' => $totalUsuarios,
            'exitosos' => $enviados,
            'fallidos' => $fallos,
        ]);
    }
}
