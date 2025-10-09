<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoDispensador extends Model
{
    protected $table = 'codigo_dispensadors';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'codigo',
    ];
}
