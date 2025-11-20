<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'user_id',
        'nombre',
        'especie',
        'raza',
        'imagen',
        'fecha_nacimiento',
        'sexo',
        'peso',
        'notas',
    ];

    // Campos que se tratan como fechas
    protected $dates = [
        'fecha_nacimiento',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Casts para convertir tipos automáticamente
    protected $casts = [
        'peso' => 'decimal:2',
        'sexo' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
