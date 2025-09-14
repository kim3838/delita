<?php

namespace App\Http\Requests\EmployeeShift;

use App\Models\EmployeeShift;

class SyncWithoutDetachingEmployeeShiftRequest extends BaseEmployeeShiftRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('syncWithoutDetaching', EmployeeShift::class);
    }
}
