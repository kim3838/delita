<?php

namespace App\Transformers\Department;

use App\Models\Department;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Department $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->name,
        ];
    }
}
