<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';

    protected $fillable = [
        'clinica_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'activo'
    ];

    public $timestamps = true;
     public function clinica()
    {
        return $this->belongsTo(\App\Models\Clinica::class, 'clinica_id');
    }
}
