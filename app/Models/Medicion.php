<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicion extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigue la convención plural)
    protected $table = 'medicions';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'dispensador_id',
        'nivel_comida',
        'peso_comida',
    ];

    /**
     * Relación con el dispensador
     */
    public function dispensador()
    {
        return $this->belongsTo(Dispensador::class);
    }
}
