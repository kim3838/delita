<?php

namespace App\Transformers\IncomeTax;

use App\Models\IncomeTax;
use League\Fractal\TransformerAbstract;

class SelectionAsComponentSubTypeTransformer extends TransformerAbstract
{
    public function transform(IncomeTax $model): array
    {
        return [
            'value' => $model->component_sub_type->value,
            'text' => $model->name,
            'type' => $model->type?->toArray(),
            'assignable' => $model->assignable,
        ];
    }
}
