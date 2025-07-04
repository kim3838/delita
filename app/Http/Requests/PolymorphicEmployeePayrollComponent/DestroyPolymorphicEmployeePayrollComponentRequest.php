<?php

namespace App\Http\Requests\PolymorphicEmployeePayrollComponent;

use App\Models\EmployeePayrollComponent;
use Illuminate\Foundation\Http\FormRequest;

class DestroyPolymorphicEmployeePayrollComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employeePayrollComponent = EmployeePayrollComponent::findOrFail($this->route('employeePayrollComponentId'));

        return $this->user()->can('delete', $employeePayrollComponent);
    }
}
