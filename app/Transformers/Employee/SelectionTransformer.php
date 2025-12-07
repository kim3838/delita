<?php

namespace App\Transformers\Employee;

use App\Models\Employee;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Employee $model): array
    {
        return [
            'value' => $model->id,
            'text' => "($model->number) " . $model->full_name,
        ];
    }
}
