<?php

namespace App\Transformers\LeaveTypeAssignment;

use App\Models\EmployeeLeaveType;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(EmployeeLeaveType $employeeLeaveType): array
    {
        return [
            'override_balance_upon_eligibility' => $employeeLeaveType->override_balance_upon_eligibility,
            'balance_upon_eligibility' => $employeeLeaveType->balance_upon_eligibility,
        ];
    }
}
