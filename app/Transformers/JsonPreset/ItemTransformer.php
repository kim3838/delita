<?php

namespace App\Transformers\JsonPreset;

use App\Models\JsonPreset;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(JsonPreset $model): array
    {
        return [
            'value' => (int)$model->id,
            'text' => $model->key,
            'json_value' => read_json($model->resource_path)
        ];
    }
}
