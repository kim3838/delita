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
            'work_start_grace_time' => $model->work_start_grace_time,
            'require_lunch_time_in_and_out' => intval($model->require_lunch_time_in_and_out),
            'lunch_start_grace_time' => $model->lunch_start_grace_time,
        ];
    }
}
