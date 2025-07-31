<?php

namespace App\Models\Hydrations;

use Illuminate\Database\Eloquent\Model;

class NonEmployeeUser extends Model
{
    protected $casts = [
        'id' => 'int',
        'ulid' => 'string',
        'name' => 'string',
        'email' => 'string',
    ];
}
