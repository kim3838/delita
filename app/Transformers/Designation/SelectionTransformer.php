<?php

namespace App\Transformers\Designation;

use App\Models\Designation;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Designation $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->name,
        ];
    }
}
