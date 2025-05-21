<?php

namespace App\Models\Hydrations\CompanyFormula;

use Illuminate\Database\Eloquent\Model;

class Selection extends Model
{
    protected $casts = [
        'id' => 'int',
        'name' => 'string',
    ];
}
