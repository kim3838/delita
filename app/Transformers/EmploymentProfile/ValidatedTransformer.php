<?php

namespace App\Transformers\EmploymentProfile;

use App\Models\EmploymentProfile;
use League\Fractal\TransformerAbstract;

class ValidatedTransformer extends TransformerAbstract
{
    public function transform(EmploymentProfile $model): array
    {
        return [
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'status' => $model->status?->toArray(),
            'employment_type' => $model->employment_type?->toArray(),
            'start_date' => $model->start_date?->toDateString(),
            'start_date_readable' => $model->start_date?->format('M d, Y'),
            'end_of_service_type' => $model->end_of_service_type?->toArray(),
            'end_date' => $model->end_date?->toDateString(),
            'end_date_readable' => $model->end_date?->format('M d, Y'),
        ];
    }
}
