<?php

namespace App\Transformers\EmployeeIdentification;

use App\Models\EmployeeIdentification;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(EmployeeIdentification $model): array
    {
        return [
            'row_number' => $model->row_number,
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'type' => $model->type?->toArray(),
            'number' => $model->number,
            'readable_number' => $model->readable_number,
            'employee' => [
                'ulid' => $model->employee->ulid,
                'number' => $model->employee->number,
                'full_name' => $model->employee->full_name_attribute,
            ],
        ];
    }
}
