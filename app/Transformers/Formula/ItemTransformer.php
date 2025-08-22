<?php

namespace App\Transformers\Formula;

use App\Models\Formula;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Formula $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'formulable_type' => $model->formulable_type->toArray(),
            'component_type' => $model->component_type ? $model->component_type->toArray() : null,
            'interpolation' => $model->interpolation,
            'default_settings' => $model->default_settings?->cast
        ];
    }
}
