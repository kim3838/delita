<?php

namespace App\Transformers\CompanyCompensation;

use App\Models\Hydrations\CompanyCompensation;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(CompanyCompensation $model): array
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'order' => $model->order,
            'assignable' => $model->assignable,
            'type' => $model->type?->toArray(),
            'company_formula_id' => $model->company_formula_id,
            'formula' => $model->formula,
            'settings' => $model->settings?->cast,
        ];
    }
}
