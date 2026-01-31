<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchUpdate', Employee::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer',

            'keep_department' => 'required|boolean',
            'department_id' => 'nullable|numeric|integer',
            'department_assignment_type' => 'nullable|numeric',

            'keep_designation' => 'required|boolean',
            'designation_id' => 'nullable|numeric|integer',

            'keep_manager' => 'required|boolean',
            'manager_id' => 'nullable|numeric|integer',

            'keep_pay_frequency' => 'required|boolean',
            'pay_frequency_id' => 'nullable|numeric|integer',

            'employee_identifiers' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'keep_department.required' => 'Keep department flag is required',
            'department_assignment_type.numeric' => 'Department assignment type must be a number',

            'keep_designation.required' => 'Keep designation flag is required',

            'keep_manager.required' => 'Keep manager flag is required',

            'keep_pay_frequency.required' => 'Keep manager flag is required',

            'employee_identifiers.required' => 'Employees are required'
        ];
    }
}
