<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThrownLog extends Model
{
    protected $fillable = [
        'thrown',
        'is_exception',
        'is_error',
        'message',
        'file',
        'line',
        'request',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'int',
        'thrown' => 'string',
        'is_exception' => 'boolean',
        'is_error' => 'boolean',
        'message' => 'string',
        'file' => 'string',
        'line' => 'int',
        'request' => 'string',
    ];
}
