<?php

namespace App\Transformers\JsonPreset;

use App\Models\JsonPreset;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(JsonPreset $model): array
    {
        return [
            'id' => (int)$model->id,
        ];
    }
}
