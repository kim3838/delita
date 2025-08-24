<?php

namespace App\Transformers\JsonPreset;

use App\Models\JsonPreset;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(JsonPreset $model): array
    {
        return [
            'row_number' => $model->row_number,
            'id' => $model->id,
            'key' => $model->key,
            'path' => $model->path,
        ];
    }
}
