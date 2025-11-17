<?php

namespace App\Transformers\Deduction;

use App\Models\Deduction;
use League\Fractal\TransformerAbstract;

class SelectionAsComponentableMorphTransformer extends TransformerAbstract
{
    public function transform(Deduction $model): array
    {
        return [
            'value' => $model->id . '.deduction',
            'text' => $model->name,
            'type' => $model->type?->toArray(),
            'assignable' => $model->assignable,
        ];
    }
}
