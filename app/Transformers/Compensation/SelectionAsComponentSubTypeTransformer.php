<?php

namespace App\Transformers\Compensation;

use App\Models\Compensation;
use League\Fractal\TransformerAbstract;

class SelectionAsComponentSubTypeTransformer extends TransformerAbstract
{
    public function transform(Compensation $model): array
    {
        return [
            'value' => $model->component_sub_type->value,
            'text' => $model->name,
            'type' => $model->type?->toArray(),
            'assignable' => $model->assignable,
        ];
    }
}
