<?php

namespace App\Transformers\EmployeeGroup;

use App\Models\Group;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Group $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->name,
        ];
    }
}
