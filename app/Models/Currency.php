<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'precision',
        'symbol',
        'symbol_native',
        'symbol_first',
        'decimal_mark',
        'thousands_separator',
    ];

    protected $casts = [
        'id' => 'int',
        'name' => 'string',
        'code' => 'string',
        'precision' => 'int',
        'symbol' => 'string',
        'symbol_native' => 'string',
        'symbol_first' => 'boolean',
        'decimal_mark' => 'string',
        'thousands_separator' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
