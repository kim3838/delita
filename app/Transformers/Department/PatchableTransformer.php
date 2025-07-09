<?php

namespace App\Transformers\Department;

use App\Models\Department;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(Department $model): array
    {
        return [
            'company_id' => $model->company_id,
            'parent_id' => $model->parent_id,
            'name' => $model->name,
        ];
    }
}
