<?php

namespace App\Transformers\EmployeeShift;

use App\Models\EmployeeShift;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(EmployeeShift $model): array
    {
        return [
            'start_date' => $model->start_date?->format('Y-m-d'),
            'stated_shift_end_date' => $model->stated_shift_end_date,
            'end_date' => $model->end_date?->format('Y-m-d'),
        ];
    }
}
