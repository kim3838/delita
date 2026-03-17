<?php

namespace App\Transformers\Holiday;

use App\Models\Holiday;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Holiday $model): array
    {
        return [
            'row_number' => $model->row_number,
            'id' => $model->id,
            'ulid' => $model->ulid,
            'name' => $model->name,
            'type' => $model->type?->toArray(),
            'holiday_pay_forfeiture' => $model->holiday_pay_forfeiture,
            'date' => $model->date->toDateString(),
            'date_readable' => $model->date->format('M d, Y'),
            'recurring' => intval($model->recurring),
            'active' => intval($model->active),
            'effective_date' => $model->effective_date->toDateString(),
            'effective_date_readable' => $model->date->format('M d, Y'),
        ];
    }
}
