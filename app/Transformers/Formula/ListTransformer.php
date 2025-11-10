<?php

namespace App\Transformers\Formula;

use App\Models\Formula;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Formula $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'name' => $model->name,
            'formulable_type' => $model->formulable_type?->toArray(),
            'component_type' => $model->component_type ? $model->component_type?->toArray() : null,
            'aggregation' => $model->aggregation,
            'default_settings' => $model->default_settings?->cast
        ];
    }
}
