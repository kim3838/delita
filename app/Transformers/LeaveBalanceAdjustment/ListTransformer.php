<?php

namespace App\Transformers\LeaveBalanceAdjustment;

use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\LeaveBalanceAdjustment;
use App\Models\LeaveType;
use App\Transformers\LeaveType\ItemTransformer as LeaveTypeItemTransformer;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(LeaveBalanceAdjustment $leaveBalanceAdjustment): array
    {
        $employee = Employee::query()->find($leaveBalanceAdjustment->employee_id);

        $leaveType = LeaveType::query()->find($leaveBalanceAdjustment->leave_type_id);

        $leaveType = $leaveType ? Fractal::item($leaveType, LeaveTypeItemTransformer::class) : $leaveType;

        return [
            'row_number' => $leaveBalanceAdjustment->row_number,
            'id' => $leaveBalanceAdjustment->id,
            'ulid' => $leaveBalanceAdjustment->ulid,
            'employee_id' => $leaveBalanceAdjustment->employee_id,
            'leave_type_id' => $leaveBalanceAdjustment->leave_type_id,
            'type' => $leaveBalanceAdjustment->type?->toArray(),
            'balance' => $leaveBalanceAdjustment->balance,
            'date' => $leaveBalanceAdjustment->effective_date->toDateString(),
            'employee' => [
                'number' => $employee->number,
                'full_name' => $employee->full_name,
                'department' => $employee->department,
                'designation' => $employee->designation,
            ],
            'leave_type' => $leaveType
        ];
    }
}
