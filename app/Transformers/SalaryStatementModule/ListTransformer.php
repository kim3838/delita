<?php

namespace App\Transformers\SalaryStatementModule;

use App\Models\SalaryStatementModule;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementModule $model): array
    {
        return [
            'id' => (int)$model->id,
            'company_id' => (int)$model->company_id,
            'formulable_type' => $model->formulable_type->toArray(),
            'order' => $model->order,
            'name' => $model->name,
            'reference' => $model->reference,
            'conditions' => $model->conditions,
        ];
    }
}
