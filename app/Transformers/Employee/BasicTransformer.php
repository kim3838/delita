<?php

namespace App\Transformers\Employee;

use App\Models\Employee;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'ulid' => $employee->ulid,
            'number' => $employee->number,
            'full_name' => $employee->full_name_attribute,
        ];
    }
}
