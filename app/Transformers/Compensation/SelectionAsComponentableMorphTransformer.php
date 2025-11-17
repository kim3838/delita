<?php

namespace App\Transformers\Compensation;

use App\Models\Compensation;
use League\Fractal\TransformerAbstract;

class SelectionAsComponentableMorphTransformer extends TransformerAbstract
{
    public function transform(Compensation $model): array
    {
        return [
            'value' => $model->id . '.compensation',
            'text' => $model->name,
            'type' => $model->type?->toArray(),
            'assignable' => $model->assignable,
        ];
    }
}
