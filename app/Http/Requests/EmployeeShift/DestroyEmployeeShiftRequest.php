<?php

namespace App\Http\Requests\EmployeeShift;

use App\Models\EmployeeShift;
use Illuminate\Foundation\Http\FormRequest;

class DestroyEmployeeShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employeeShift = EmployeeShift::query()->findOrFail($this->route('employeeShiftId'));

        return $this->user()->can('delete', $employeeShift);
    }
}
