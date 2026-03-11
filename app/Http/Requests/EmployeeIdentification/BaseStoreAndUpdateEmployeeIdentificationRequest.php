<?php

namespace App\Http\Requests\EmployeeIdentification;

use Illuminate\Foundation\Http\FormRequest;

class BaseStoreAndUpdateEmployeeIdentificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'number' => 'required|string|max:255',
            'readable_number' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee account is required',
            'type.required' => 'Type is required',
            'type.max' => 'Type must not be greater than 255 characters',

            'number.required' => 'Number is required',
            'number.max' => 'Number must not be greater than 255 characters',

            'readable_number.max' => 'Readable number must not be greater than 255 characters',
        ];
    }
}
