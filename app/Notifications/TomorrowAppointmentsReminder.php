<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TomorrowAppointmentsReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $citasResumen;

    public function __construct(array $citasResumen)
    {
        $this->citasResumen = $citasResumen; // array de [['mascota' => ..., 'servicio' => ..., 'fecha' => ...], ...]
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Recordatorio: citas para mañana')
            ->greeting('Hola ' . ($notifiable->nombre ?? ''))
            ->line('Tienes citas agendadas para mañana:');

        foreach ($this->citasResumen as $cita) {
            $line = sprintf('- %s | %s | %s', $cita['mascota'] ?? 'Mascota', $cita['servicio'] ?? 'Servicio', $cita['fecha'] ?? '');
            $mail->line($line);
        }

        $mail->line('Por favor, llega con unos minutos de anticipación.');

        return $mail;
    }
}
