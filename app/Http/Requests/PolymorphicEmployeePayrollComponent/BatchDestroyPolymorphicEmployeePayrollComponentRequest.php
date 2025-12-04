<?php

namespace App\Http\Requests\PolymorphicEmployeePayrollComponent;

use App\Models\EmployeePayrollComponent;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyPolymorphicEmployeePayrollComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', EmployeePayrollComponent::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'payroll_component_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'payroll_component_ids.required' => 'Payroll component ids is required',
            'payroll_component_ids.array' => 'Payroll component ids must be an array',
        ];
    }
}
