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
            'start_date_readable' => $model->start_date?->format('M j, Y'),
            'stated_shift_end_date' => intval($model->stated_shift_end_date),
            'end_date' => $model->end_date?->format('Y-m-d'),
            'end_date_readable' => $model->end_date?->format('M j, Y'),
            'readable_date_range' => $model->stated_shift_end_date
                ? $model->start_date?->format('M j, Y') . " to " . $model->end_date?->format('M j, Y')
                : $model->start_date?->format('M j, Y') . " onwards."
        ];
    }
}
