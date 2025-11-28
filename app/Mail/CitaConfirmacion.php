<?php

namespace App\Mail;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Mascota;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CitaConfirmacion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Mascota $mascota,
        public Cita $cita,
        public ?Clinica $clinica = null,
        public ?Servicio $servicio = null,
    ) {}

    public function build()
    {
        return $this
            ->subject('Confirmación de cita')
            ->view('emails.cita_confirmacion')
            ->with([
                'user' => $this->user,
                'mascota' => $this->mascota,
                'cita' => $this->cita,
                'clinica' => $this->clinica,
                'servicio' => $this->servicio,
            ]);
    }
}
