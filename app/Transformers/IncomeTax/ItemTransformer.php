<?php

namespace App\Transformers\IncomeTax;

use App\Models\IncomeTax;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(IncomeTax $model): array
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'name' => $model->name,
            'order' => $model->order,
            'assignable' => $model->assignable,
            'type' => $model->type->toArray(),
            'formula' => $model->companyFormula->formula->name,
        ];
    }
}
