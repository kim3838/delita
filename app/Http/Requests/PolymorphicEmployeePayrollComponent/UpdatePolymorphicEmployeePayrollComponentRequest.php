<?php

namespace App\Http\Requests\PolymorphicEmployeePayrollComponent;

use App\Models\EmployeePayrollComponent;

class UpdatePolymorphicEmployeePayrollComponentRequest extends BasePolymorphicEmployeePayrollComponentRequest
{
    public function authorize(): bool
    {
        $employeePayrollComponent = EmployeePayrollComponent::query()->findOrFail($this->route('employeePayrollComponentId'));

        return $this->user()->can('update', $employeePayrollComponent);
    }
}
