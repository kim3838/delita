<?php

namespace App\Transformers\PayFrequency;

use App\Models\PayFrequency;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(PayFrequency $model): array
    {
        return [
            'value' => $model->id,
            'code' => $model->code,
            'type_value' => $model->type->value,
            'text' => $model->type->label(),
        ];
    }
}
