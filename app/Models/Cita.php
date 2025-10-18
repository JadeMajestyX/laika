<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Clinica;
use App\Models\Servicio;
use App\Models\Mascota;
use App\Models\User;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'clinica_id',
        'servicio_id',
        'mascota_id',
        'creada_por',
        'fecha',
        'notas',
        'status',
    ];

    protected $casts = [
    'fecha' => 'datetime',
    ];

    /**
     * Relación: una cita pertenece a una clínica.
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Relación: una cita pertenece a un servicio.
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    /**
     * Relación: una cita pertenece a una mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    /**
     * Relación: usuario que creó la cita.
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

}
