<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    protected $fillable = ['cita_id', 'veterinario_id', 'diagnostico', 'notas'];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecetaItem::class);
    }
}
