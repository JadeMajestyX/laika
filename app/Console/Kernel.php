<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecutar diariamente a las 00:10
        $schedule->command('citas:cancelar-pasadas')->dailyAt('00:10');

        // Para entornos de prueba se puede activar cada minuto (comentar en producción):
        // $schedule->command('citas:cancelar-pasadas')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
