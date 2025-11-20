<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordCodeNotification extends Notification
{
    use Queueable;

    /**
     * Código de 6 dígitos para restablecer la contraseña.
     */
    public string $code;

    /**
     * Minutos de vigencia del código.
     */
    public int $expiresInMinutes;

    public function __construct(string $code, int $expiresInMinutes = 15)
    {
        $this->code = $code;
        $this->expiresInMinutes = $expiresInMinutes;
    }

    /**
     * Canales de notificación.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Contenido del correo electrónico.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Código para restablecer tu contraseña')
            ->greeting('Hola ' . ($notifiable->nombre ?? ''))
            ->line('Hemos recibido una solicitud para restablecer tu contraseña.')
            ->line('Tu código de verificación es:')
            ->line('')
            ->line('🔐 ' . $this->code)
            ->line('')
            ->line('Este código expira en ' . $this->expiresInMinutes . ' minutos.')
            ->line('Si tú no solicitaste este cambio, puedes ignorar este correo.');
    }
}
