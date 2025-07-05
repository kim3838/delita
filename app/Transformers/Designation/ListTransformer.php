<?php

namespace App\Transformers\Designation;

use App\Models\Designation;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Designation $model): array
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'name' => $model->name,
        ];
    }
}
