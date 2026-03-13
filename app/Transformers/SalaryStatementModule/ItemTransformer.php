<?php

namespace App\Transformers\SalaryStatementModule;

use App\Models\SalaryStatementModule;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementModule $model): array
    {
        return [
            'id' => (int)$model->id,
            'company_id' => (int)$model->company_id,
            'order' => $model->order,
            'key' => $model->key,
            'name' => $model->name,
            'formulable_type' => $model->formulable_type?->toArray(),
            'aggregation' => $model->aggregation,
            'statement_level' => $model->statement_level,
            'property' => $model->property,
            'attribute' => $model->attribute,
            'conditions' => $model->conditions,
        ];
    }
}
