<?php

namespace App\Transformers\CompanyDeduction;

use App\Models\Hydrations\CompanyDeduction;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(CompanyDeduction $model): array
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'name' => $model->name,
            'order' => $model->order,
            'assignable' => $model->assignable,
            'type' => $model->type->toArray(),
            'company_formula_id' => $model->company_formula_id,
            'formula' => $model->formula,
            'settings' => $model->settings,
        ];
    }
}
