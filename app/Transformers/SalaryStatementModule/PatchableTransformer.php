<?php

namespace App\Transformers\SalaryStatementModule;

use App\Models\SalaryStatementModule;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementModule $model): array
    {
        return [
            'id' => (int)$model->id,
            'company_id' => (int)$model->company_id,
            'name' => $model->name,
            'formulable_type' => $model->formulable_type?->value,
            'aggregation' => $model->aggregation,
            'property' => $model->property,
            'attribute' => $model->attribute,
            'conditions' => $model->conditions ? $model->conditions : null,
        ];
    }
}
