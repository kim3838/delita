<?php

namespace App\Transformers\Deduction;

use App\Models\Deduction;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Deduction $model): array
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'order' => $model->order,
            'assignable' => $model->assignable,
            'type' => $model->type?->toArray(),
            'formula' => $model->companyFormula->formula->name
        ];
    }
}
