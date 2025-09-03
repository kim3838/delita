<?php

namespace App\Http\Requests\EmploymentProfile;

use Illuminate\Foundation\Http\FormRequest;

class BaseStoreAndUpdateEmploymentProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => 'required|numeric|integer',
            'employment_type' => 'required|numeric|integer',
            'start_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'end_of_service_type' => 'sometimes|required|numeric|integer',
            'end_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee account is required',
            'start_date.date_format' => 'Start date must match the format Y-m-d (2000-01-01)',
            'end_date.date_format' => 'End date must match the format Y-m-d (2000-01-01)',
        ];
    }
}
