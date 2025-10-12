<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dispensador extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigue la convención plural)
    protected $table = 'dispensadors';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'usuario_id',
        'codigo_dispensador_id',
        'nombre',
        'mascota_id',
    ];

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la mascota
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    /**
     * Relación con el código de dispensador
     */
    public function codigoDispensador()
    {
        return $this->belongsTo(CodigoDispensador::class);
    }
}
