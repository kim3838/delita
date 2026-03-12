<?php

namespace App\Models\Hydrations;

use App\Casts\Parsable;
use App\Enums\Deduction as DeductionEnum;
use App\Enums\FormulableComponentSubType;
use Illuminate\Database\Eloquent\Model;

class CompanyDeduction extends Model
{
    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'code' => 'string',
        'name' => 'string',
        'order' => 'int',
        'assignable' => 'boolean',
        'type' => DeductionEnum::class,
        'component_sub_type' => FormulableComponentSubType::class,
        'company_formula_id' => 'int',
        'formula' => 'string',
        'settings' => Parsable::class
    ];
}
