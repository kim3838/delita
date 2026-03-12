<?php

namespace App\Models\Hydrations;

use App\Casts\Parsable;
use App\Enums\FormulableComponentSubType;
use App\Enums\IncomeTax as IncomeTaxEnum;
use Illuminate\Database\Eloquent\Model;

class CompanyIncomeTax extends Model
{
    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'code' => 'string',
        'name' => 'string',
        'order' => 'int',
        'assignable' => 'boolean',
        'type' => IncomeTaxEnum::class,
        'component_sub_type' => FormulableComponentSubType::class,
        'company_formula_id' => 'int',
        'formula' => 'string',
        'settings' => Parsable::class
    ];
}
