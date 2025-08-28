<?php

namespace App\Transformers\CompanyFormula;

use App\Models\Hydrations\CompanyFormula\FormulaSetting;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(FormulaSetting $model): array
    {
        return [
            'id' => $model->company_formula_id,
            'company_formula_id' => $model->company_formula_id,
            'company_id' => $model->company_id,
            'formula_id' => $model->formula_id,
            'formula_ulid' => $model->formula_ulid,
            'company_code' => $model->company_code,
            'company_name' => $model->company_name,
            'formula_name' => $model->formula_name,
            'formula_is_interpolation' => $model->formula_is_interpolation,
            'formulable_type' => $model->formulable_type->toArray(),
            'formulable_component_type' => ($model->formulable_component_type)
                ? $model->formulable_component_type->toArray()
                : null,
            'default_settings' => $model->default_settings?->cast,
            'sub_row' => [
                'settings' => $model->formula_settings?->cast
            ],
        ];
    }
}
