<?php

namespace App\Transformers\EmploymentProfile;

use App\Models\EmploymentProfile;
use League\Fractal\TransformerAbstract;

class CurrentEmploymentProfileTransformer extends TransformerAbstract
{
    public function transform(EmploymentProfile $model): array
    {
        return [
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'is_active' => (boolean)$model->is_active,
            'status' => $model->status?->toArray(),
            'employment_type' => $model->employment_type?->toArray(),
            'start_date' => $model->start_date?->format('Y-m-d'),
            'start_date_readable' => $model->start_date?->format('M d, Y'),
            'end_of_service_type' => $model->end_of_service_type?->toArray(),
            'end_date' => $model->end_date?->format('Y-m-d'),
            'end_date_readable' => $model->end_date?->format('M d, Y'),
            'readable_date_range' => $model->end_of_service_type
                ? $model->start_date?->format('M d, Y>') . " to " . $model->end_date?->format('M d, Y')
                : $model->start_date?->format('M d, Y') . " onwards."
        ];
    }
}
