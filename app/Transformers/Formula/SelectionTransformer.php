<?php

namespace App\Transformers\Formula;

use App\Models\Formula;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Formula $model): array
    {
        return [
            'value' => $model->ulid,
            'text' => $model->name,
        ];
    }
}
