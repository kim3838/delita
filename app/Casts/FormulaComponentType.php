<?php

namespace App\Casts;

use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\IncomeTax;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class FormulaComponentType implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $formulable_type = $model->formulable_type;

        return match($formulable_type){
            Formulable::EARNINGS => !is_null($value) ? Compensation::tryFrom($value) : null,
            Formulable::DEDUCTIONS => !is_null($value) ? Deduction::tryFrom($value) : null,
            Formulable::INCOME_TAX => !is_null($value) ? IncomeTax::tryFrom($value) : null,
            default => null,
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }
}
