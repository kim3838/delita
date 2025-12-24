<?php

namespace App\Http\Requests\EmployeeShift;

use App\Models\EmployeeShift;

class SyncEmployeeShiftRequest extends BaseEmployeeShiftRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sync', EmployeeShift::class);
    }
}
