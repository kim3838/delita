<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timezone extends Model
{
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'id' => 'int',
        'name' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
