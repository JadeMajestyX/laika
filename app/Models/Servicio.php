<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'clinica_id',
        'nombre',
        'descripcion',
        'precio',
        'tiempo_estimado',
    ];

    /**
     * Relación: un servicio pertenece a una clínica.
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * (Opcional) Si un servicio se usa en citas, puedes agregar esta relación.
     */
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}
