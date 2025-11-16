<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    
    protected $table = 'statuses';

    protected $fillable = ['status', 'calibrar', 'dispensador_id'];

        protected $casts = [
        'status' => 'boolean',
        'calibrar' => 'boolean',
    ];
}
