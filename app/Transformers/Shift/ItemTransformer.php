<?php

namespace App\Transformers\Shift;

use App\Models\Shift;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Shift $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'type' => $model->type->toArray(),
        ];
    }
}
