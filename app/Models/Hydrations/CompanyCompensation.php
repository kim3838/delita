<?php

namespace App\Models\Hydrations;

use App\Enums\Compensation;
use Illuminate\Database\Eloquent\Model;

class CompanyCompensation extends Model
{
    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'name' => 'string',
        'order' => 'int',
        'assignable' => 'boolean',
        'type' => Compensation::class,
        'company_formula_id' => 'int',
        'formula' => 'string',
        'settings' => 'object'
    ];
}
