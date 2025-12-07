<?php

namespace App\Http\Requests\EmployeeLeaveType;

use App\Models\EmployeeLeaveType;

class SyncWithoutDetachingEmployeeLeaveTypeRequest extends BaseEmployeeLeaveTypeRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('syncWithoutDetaching', EmployeeLeaveType::class);
    }
}
