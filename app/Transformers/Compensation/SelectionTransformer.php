<?php

namespace App\Transformers\Compensation;

use App\Models\Compensation;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Compensation $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->code . ' - ' . $model->name,
            'type' => $model->type?->toArray(),
            'assignable' => $model->assignable,
        ];
    }
}
