<?php

namespace App\Http\Requests\PayrollComponent;

use Illuminate\Foundation\Http\FormRequest;

class BasePayrollComponentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'assignable' => 'required|boolean',
            'type' => 'required|numeric',
            'company_formula_id' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'assignable.required' => 'Assignable is required',
            'type.required' => 'Type is required',
            'company_formula_id.required' => 'Formula is required',
        ];
    }
}
