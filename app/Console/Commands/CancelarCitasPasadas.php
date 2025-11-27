<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class CancelarCitasPasadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'citas:cancelar-pasadas {--dry-run : Solo muestra cuántas se cancelarían sin aplicar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancela automáticamente todas las citas pasadas no completadas ni canceladas.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ahora = Carbon::now();

        $citasQuery = Cita::where('fecha', '<', $ahora)
            ->whereNotIn('status', ['completada', 'cancelada']);

        $total = $citasQuery->count();

        if ($total === 0) {
            $this->info('No hay citas pasadas pendientes por cancelar.');
            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $idsPreview = $citasQuery->limit(10)->pluck('id')->toArray();
            $this->line("Se cancelarían {$total} citas (primeras 10 IDs): " . implode(', ', $idsPreview));
            return Command::SUCCESS;
        }

        $citas = $citasQuery->get();
        $canceladas = 0;
        $ids = [];

        // Request vacío para ActivityLogger (sin contexto HTTP real)
        $fakeRequest = new Request();

        foreach ($citas as $cita) {
            $cita->status = 'cancelada';
            $cita->save();
            $canceladas++;
            $ids[] = $cita->id;

            // Registrar actividad (user_id null porque es tarea del sistema)
            ActivityLogger::log($fakeRequest, 'Cancelar cita pasada (scheduler)', 'Cita', $cita->id, [
                'motivo' => 'Auto-cancelación por fecha pasada (scheduler)',
                'fecha' => $cita->fecha,
            ], null);
        }

        $this->info("Total revisadas: {$total} | Total canceladas: {$canceladas}");
        Log::info('CancelarCitasPasadas: canceladas ' . $canceladas . ' de ' . $total, ['ids' => $ids]);

        return Command::SUCCESS;
    }
}
