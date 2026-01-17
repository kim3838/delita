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
            'employee' => [
                'id' => $employee->id,
                'number' => $employee->number,
                'full_name' => $employee->full_name,
                'department' => $employee->departments->first(),
                'designation' => $employee->designation,
            ],
            'leave_type' => $leaveType
        ];
    }
}
