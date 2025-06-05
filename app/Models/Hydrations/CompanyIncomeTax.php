<?php

namespace App\Models\Hydrations;

use App\Enums\IncomeTax as IncomeTaxEnum;
use Illuminate\Database\Eloquent\Model;

class CompanyIncomeTax extends Model
{
    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'name' => 'string',
        'order' => 'int',
        'assignable' => 'boolean',
        'type' => IncomeTaxEnum::class,
        'company_formula_id' => 'int',
        'formula' => 'string',
        'settings' => 'object'
    ];
}
