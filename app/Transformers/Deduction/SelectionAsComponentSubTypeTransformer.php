<?php

namespace App\Transformers\Deduction;

use App\Models\Deduction;
use League\Fractal\TransformerAbstract;

class SelectionAsComponentSubTypeTransformer extends TransformerAbstract
{
    public function transform(Deduction $model): array
    {
        return [
            'value' => $model->component_sub_type->value,
            'text' => $model->name,
            'type' => $model->type?->toArray(),
            'assignable' => $model->assignable,
        ];
    }
}
