<?php

namespace App\Transformers\ShiftAssignment;

use App\Models\EmployeeShift;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(EmployeeShift $employeeShift): array
    {
        return [
            'start_date' => $employeeShift->start_date?->format('Y-m-d'),
            'stated_shift_end_date' => $employeeShift->stated_shift_end_date,
            'end_date' => $employeeShift->end_date?->format('Y-m-d'),
        ];
    }
}
