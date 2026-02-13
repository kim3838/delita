<?php

namespace App\Transformers\SalaryStatementModule;

use App\Models\SalaryStatementModule;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementModule $model): array
    {
        return [
            'id' => (int)$model->id,
            'order' => $model->order,
            'key' => $model->key,
            'name' => $model->name,
            'formulable_type_name' => $model->formulable_type?->label(),
        ];
    }
}
