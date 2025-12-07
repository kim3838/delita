<?php

namespace App\Http\Requests\EmployeeLeaveType;

use App\Models\EmployeeLeaveType;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyEmployeeLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', EmployeeLeaveType::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'leave_type_assignment_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'leave_type_assignment_ids.required' => 'Leave type assignment ids is required',
            'leave_type_assignment_ids.array' => 'Leave type assignment ids must be an array',
        ];
    }
}
