<?php

namespace App\Http\Requests\EmployeeIdentification;

use App\Models\EmployeeIdentification;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyEmployeeIdentificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', EmployeeIdentification::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'employee_identification_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'employee_identification_ids.required' => 'Employee identification ids is required',
            'employee_identification_ids.array' => 'Employee identification ids must be an array',
        ];
    }
}
