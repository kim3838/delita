<?php

namespace App\Http\Requests\Leave;

use App\Models\Leave;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Leave::class);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|numeric|exists:employees,id',
            'leave_type_id' => 'required|numeric|exists:leave_types,id',
            'date' => 'required|date|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.exists' => 'Employee not found',
            'employee_id.required' => 'Employee is required',
            'employee_id.numeric' => 'Employee id must be numeric',
            'leave_type_id.exists' => 'Leave type not found',
            'leave_type_id.required' => 'Leave type is required',
            'leave_type_id.numeric' => 'Leave type id must be numeric',
            'date.date_format' => 'Date must match the format Y-m-d e.g.(2000-12-31)',
        ];
    }
}
