<?php

namespace App\Transformers\EmploymentProfile;

use App\Models\EmploymentProfile;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(EmploymentProfile $model): array
    {
        return [
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'status' => $model->status?->value,
            'employment_type' => $model->employment_type?->value,
            'start_date' => $model->start_date?->format('Y-m-d'),
            'end_of_service_type' => $model->end_of_service_type?->value,
            'end_date' => $model->end_date?->format('Y-m-d'),
        ];
    }
}
