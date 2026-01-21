<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class BaseEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|numeric|integer',
            'company_id' => 'required|numeric|integer',
            'department_id' => 'nullable|numeric|integer',
            'designation_id' => 'nullable|numeric|integer',
            'manager_id' => 'nullable|numeric|integer',
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
            'family_name.required' => 'Family name is required',
            'given_name.required' => 'Given name is required',
            'birth_date.required' => 'Birth date is required',
            'gender.required' => 'Gender is required',
            'marital_status.required' => 'Marital status is required',
            'number.required' => 'Employee number is required',
            'number.unique' =>  'Employee number has already been taken',
            'number.regex' => 'Employee number must not contain spaces',
        ];
    }
}
