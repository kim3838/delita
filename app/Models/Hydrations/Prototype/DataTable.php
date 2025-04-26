<?php

namespace App\Models\Hydrations\Prototype;

use Illuminate\Database\Eloquent\Model;

class DataTable extends Model
{
    protected $casts = [
        'row_number' => 'int',
        'id' => 'int',
        'name' => 'string',
        'code' => 'string',
        'type' => 'int',
        'category' => 'int',
        'capacity' => 'int',
        'json_data' => 'array',
        'datetime_added' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
