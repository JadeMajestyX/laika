<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\User;
use App\Services\FcmV1Client;
use App\Notifications\TomorrowAppointmentsReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class RecordatorioCitasManana extends Command
{
    protected $signature = 'citas:recordatorio-manana {--dry-run}';
    protected $description = 'Enviar recordatorio (push y correo) a usuarios con citas para mañana';

    public function handle(): int
    {
        $mananaInicio = Carbon::tomorrow()->startOfDay();
        $mananaFin = Carbon::tomorrow()->endOfDay();

        $citas = Cita::with(['mascota', 'servicio'])
            ->whereBetween('fecha', [$mananaInicio, $mananaFin])
            ->whereNotIn('status', ['cancelada'])
            ->get();

        // Agrupar por usuario dueño de la mascota
        $porUsuario = [];
        foreach ($citas as $cita) {
            $userId = $cita->mascota->user_id ?? null;
            if (!$userId) { continue; }
            $porUsuario[$userId] ??= [];
            $porUsuario[$userId][] = [
                'mascota' => $cita->mascota->nombre ?? null,
                'servicio' => $cita->servicio->nombre ?? null,
                'fecha' => (string)$cita->fecha,
            ];
        }

        $dry = $this->option('dry-run');
        $client = new FcmV1Client();
        $totalUsuarios = count($porUsuario);
        $pushOk = 0; $mailOk = 0;

        foreach ($porUsuario as $userId => $resumen) {
            $user = User::find($userId);
            if (!$user) { continue; }

            $title = 'Recordatorio de citas para mañana';
            $body = 'Tienes ' . count($resumen) . ' cita(s) programada(s) para mañana.';
            $dataPayload = ['tipo' => 'recordatorio_citas', 'count' => (string)count($resumen)];

            if (!$dry) {
                // Push
                try {
                    $sum = $client->sendToUser($user->id, $title, $body, $dataPayload);
                    if (($sum['success'] ?? 0) > 0) { $pushOk++; }
                } catch (\Throwable $e) {
                    // continuar
                }

                // Mail
                try {
                    Notification::send($user, new TomorrowAppointmentsReminder($resumen));
                    $mailOk++;
                } catch (\Throwable $e) {
                    // continuar
                }
            } else {
                $this->info("DRY: usuario {$user->id} tendría " . count($resumen) . " citas");
            }
        }

        $this->info("Usuarios con citas mañana: {$totalUsuarios} | push OK: {$pushOk} | mail OK: {$mailOk}");
        return 0;
    }
}
