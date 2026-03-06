<?php

namespace App\Transformers\EmploymentProfile;

use App\Models\EmploymentProfile;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(EmploymentProfile $model): array
    {
        return [
            'row_number' => $model->row_number,
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'status' => $model->status?->toArray(),
            'employment_type' => $model->employment_type?->toArray(),
            'start_date' => $model->start_date?->format('Y-m-d'),
            'start_date_readable' => $model->start_date?->format('M j, Y'),
            'end_of_service_type' => $model->end_of_service_type?->toArray(),
            'end_date' => $model->end_date?->format('Y-m-d'),
            'end_date_readable' => $model->end_date?->format('M j, Y'),
            'created_at' => $model->created_at?->format('Y-m-d'),
            'employee' => [
                'ulid' => $model->employee->ulid,
                'number' => $model->employee->number,
                'full_name' => $model->employee->full_name,
            ],
        ];
    }
}
