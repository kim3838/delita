<?php

namespace App\Transformers\EmployeeGroup;

use App\Models\Group;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Group $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'name' => $model->name,
        ];
    }
}
