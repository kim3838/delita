<?php

namespace App\Http\Requests\EmployeePortal\OvertimeRequest;

use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyOvertimeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'overtime_request_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'overtime_request_ids.required' => 'Overtime ids is required',
            'overtime_request_ids.array' => 'Overtime ids must be an array',
        ];
    }
}
