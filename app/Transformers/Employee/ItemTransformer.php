<?php

namespace App\Transformers\Employee;

use App\Models\Employee;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Employee $employee)
    {
        return [
            'id' => $employee->id,
            'ulid' => $employee->ulid,
            'number' => $employee->number,
            'full_name' => $employee->full_name,
            'given_name' => $employee->given_name,
            'middle_name' => $employee->middle_name,
            'family_name' => $employee->family_name,
            'birth_date' => Carbon::parse($employee->birth_date)->toDateString(),
            'gender' => $employee->gender->toArray(),
            'marital_status' => $employee->marital_status->toArray(),
        ];
    }
}
