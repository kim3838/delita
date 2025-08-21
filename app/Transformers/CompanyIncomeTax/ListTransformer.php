<?php

namespace App\Transformers\CompanyIncomeTax;

use App\Models\Hydrations\CompanyIncomeTax;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(CompanyIncomeTax $model): array
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'order' => $model->order,
            'assignable' => $model->assignable,
            'type' => $model->type->toArray(),
            'company_formula_id' => $model->company_formula_id,
            'formula' => $model->formula,
            'settings' => $model->settings?->cast,
        ];
    }
}
