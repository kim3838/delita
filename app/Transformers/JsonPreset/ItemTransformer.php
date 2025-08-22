<?php

namespace App\Transformers\JsonPreset;

use App\Models\JsonPreset;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(JsonPreset $model): array
    {
        return [
            'value' => (int)$model->id,
            'text' => $model->key,
            'path' => $model->path,
            'json_value' => Storage::disk($model->disk)->json($model->path)
        ];
    }
}
