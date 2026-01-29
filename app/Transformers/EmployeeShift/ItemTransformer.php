<?php

namespace App\Transformers\EmployeeShift;

use App\Models\EmployeeShift;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(EmployeeShift $model): array
    {
        return [
            'start_date' => $model->start_date?->format('Y-m-d'),
            'stated_shift_end_date' => intval($model->stated_shift_end_date),
            'end_date' => $model->end_date?->format('Y-m-d'),
            'readable_date_range' => $model->stated_shift_end_date
                ? $model->start_date?->format('Y-m-d') . " to " . $model->end_date?->format('Y-m-d')
                : $model->start_date?->format('Y-m-d') . " onwards."
        ];
    }
}
