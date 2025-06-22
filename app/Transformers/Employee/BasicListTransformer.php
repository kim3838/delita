<?php

namespace App\Transformers\Employee;

use App\Models\Employee;
use League\Fractal\TransformerAbstract;

class BasicListTransformer extends TransformerAbstract
{
    public function transform(Employee $employee)
    {
        return [
            'id' => $employee->id,
            'ulid' => $employee->ulid,
            'row_number' => $employee->row_number,
            'number' => $employee->number,
            'full_name' => $employee->full_name,
            'gender' => $employee->gender->toArray(),
            'marital_status' => $employee->marital_status->toArray()
        ];
    }
}
