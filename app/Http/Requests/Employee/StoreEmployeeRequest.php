<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Employee::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|numeric|integer',
            'company_id' => 'required|numeric|integer',
            'department_id' => 'nullable|numeric|integer',
            'designation_id' => 'nullable|numeric|integer',
            'manager_id' => 'nullable|numeric|integer',
            'number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees')
            ],
            'family_name' => 'required|string|max:255',
            'given_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|numeric|integer',
            'marital_status' => 'required|numeric|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User account is required',
            'company_id.required' => 'Company is required',
            'number.required' => 'Employee number is required',
            'number.unique' =>  'Employee number has already been taken',
        ];
    }
}
