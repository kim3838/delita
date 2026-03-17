<?php

namespace App\Transformers\EmployeeEmploymentProfile;

use App\Models\EmploymentProfile;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(EmploymentProfile $model): array
    {
        return [
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'employee_number' => $model->employee->_number,
            'employee_full_name' => $model->employee->_full_name,
            'status' => $model->status?->toArray(),
            'employment_type' => $model->employment_type?->toArray(),
            'start_date' => $model->start_date?->format('Y-m-d'),
            'start_date_readable' => $model->start_date?->format('M d, Y'),
            'end_of_service_type' => $model->end_of_service_type?->toArray(),
            'end_date' => $model->end_date?->format('Y-m-d'),
            'end_date_readable' => $model->end_date?->format('M d, Y'),
        ];
    }
}
