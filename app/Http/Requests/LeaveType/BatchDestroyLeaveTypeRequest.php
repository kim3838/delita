<?php

namespace App\Http\Requests\LeaveType;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', LeaveType::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'leave_type_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'leave_type_ids.required' => 'Leave type ids is required',
            'leave_type_ids.array' => 'Leave type ids must be an array',
        ];
    }
}
