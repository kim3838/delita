<?php

namespace App\Transformers\Deduction;

use App\Models\Deduction;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Deduction $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->name,
            'type' => $model->type?->toArray(),
            'assignable' => $model->assignable,
        ];
    }
}
