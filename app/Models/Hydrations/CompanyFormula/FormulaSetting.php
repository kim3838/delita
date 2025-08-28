<?php

namespace App\Models\Hydrations\CompanyFormula;

use App\Casts\FormulaComponentType;
use App\Casts\Parsable;
use App\Enums\Formulable;
use Illuminate\Database\Eloquent\Model;

class FormulaSetting extends Model
{
    protected $casts = [
        'company_formula_id' => 'int',
        'company_id' => 'int',
        'formula_id' => 'int',
        'formula_ulid' => 'string',
        'company_code' => 'string',
        'company_name' => 'string',
        'formula_name' => 'string',
        'formula_is_interpolation' => 'bool',
        'formulable_type' => Formulable::class,
        'formulable_component_type' => FormulaComponentType::class,
        'default_settings' => Parsable::class,
        'formula_settings' => Parsable::class,
    ];
}
