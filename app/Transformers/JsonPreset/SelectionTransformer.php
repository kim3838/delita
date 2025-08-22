<?php

namespace App\Transformers\JsonPreset;

use App\Models\JsonPreset;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(JsonPreset $model): array
    {
        return [
            'value' => (int)$model->id,
            'text' => $model->path
        ];
    }
}
