<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cita;

class Clinica extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla (opcional si sigue la convención)
    protected $table = 'clinicas';

    // Los campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'is_open',
        'is_visible',
        'site',
    ];

    // Los campos que se tratan como fechas automáticamente
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relación con otras tablas si lo necesitas, por ejemplo con citas:
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}
