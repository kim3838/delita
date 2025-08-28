<?php

namespace App\Transformers\Formula;

use App\Models\Formula;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(Formula $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'name' => $model->name,
            'formulable_type' => $model->formulable_type?->value,
            'component_type' => $model->component_type?->value,
            'interpolation' => $model->interpolation,
            'default_settings' => $model->default_settings?->cast
        ];
    }
}
