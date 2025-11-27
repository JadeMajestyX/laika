<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id', 'title', 'body', 'data_json', 'tokens_json', 'success', 'fail', 'total', 'results_json'
    ];
}
