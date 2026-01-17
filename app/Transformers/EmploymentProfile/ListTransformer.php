<?php

namespace App\Transformers\EmploymentProfile;

use App\Models\Employee;
use App\Models\EmploymentProfile;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(EmploymentProfile $model): array
    {
        $employee = Employee::query()->find($model->employee_id);

        return [
            'row_number' => $model->row_number,
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'status' => $model->status?->toArray(),
            'employment_type' => $model->employment_type?->toArray(),
            'start_date' => $model->start_date?->format('Y-m-d'),
            'end_of_service_type' => $model->end_of_service_type?->toArray(),
            'end_date' => $model->end_date?->format('Y-m-d'),
            'created_at' => $model->created_at?->format('Y-m-d'),
            'employee' => [
                'ulid' => $employee->ulid,
                'number' => $employee->number,
                'full_name' => $employee->full_name,
                'department' => $employee->departments->first(),
                'designation' => $employee->designation,
            ],
        ];
    }
}
