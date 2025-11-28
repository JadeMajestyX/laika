<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecetaItem extends Model
{
    protected $fillable = ['receta_id', 'medicamento', 'dosis', 'notas'];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class);
    }
}
