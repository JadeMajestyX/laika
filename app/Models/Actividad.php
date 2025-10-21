<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividades';

    protected $fillable = [
        'user_id',
        'accion',
        'modelo',
        'modelo_id',
        'detalles',
        'ip_address',
        'navegador',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
