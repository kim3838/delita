<?php

namespace App\Http\Requests\EmployeePortal\LeaveRequest;

use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'leave_request_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'leave_request_ids.required' => 'Leave request ids is required',
            'leave_request_ids.array' => 'Leave request ids must be an array',
        ];
    }
}
