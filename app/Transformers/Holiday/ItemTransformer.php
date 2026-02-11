<?php

namespace App\Transformers\Holiday;

use App\Models\Holiday;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Holiday $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'name' => $model->name,
            'type' => $model->type?->toArray(),
            'holiday_pay_forfeiture' => $model->holiday_pay_forfeiture,
            'date' => $model->date->toDateString(),
            'recurring' => $model->recurring,
            'active' => $model->active,
            'effective_date' => $model->effective_date->toDateString(),
        ];
    }
}
