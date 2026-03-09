<?php

namespace App\Transformers\Leave;

use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Transformers\LeaveType\ItemTransformer as LeaveTypeItemTransformer;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Leave $leave): array
    {
        $employee = Employee::query()->find($leave->employee_id);

        $leaveType = LeaveType::query()->find($leave->leave_type_id);

        $leaveType = $leaveType ? Fractal::item($leaveType, LeaveTypeItemTransformer::class) : $leaveType;

        return [
            'row_number' => $leave->row_number,
            'id' => $leave->id,
            'ulid' => $leave->ulid,
            'date' => $leave->date->toDateString(),
            'date_readable' => $leave->date->format('M j, Y'),
            'employee' => [
                'id' => $employee->id,
                'number' => $leave->employee_number,
                'full_name' => $leave->employee_full_name,
            ],
            'leave_type' => $leaveType
        ];
    }
}
