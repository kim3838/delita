<?php

namespace App\Transformers\EmployeeEmployeeIdentification;

use App\Models\EmployeeIdentification;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(EmployeeIdentification $model): array
    {
        return [
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'employee_number' => $model->employee->_number,
            'employee_full_name' => $model->employee->_full_name,
            'type' => $model->type?->toArray(),
            'number' => $model->number,
            'readable_number' => $model->readable_number,
        ];
    }
}
