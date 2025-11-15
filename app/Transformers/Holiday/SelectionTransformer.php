<?php

namespace App\Transformers\Holiday;

use App\Models\Holiday;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Holiday $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->name
        ];
    }
}
