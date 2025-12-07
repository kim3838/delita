<?php

namespace App\Http\Requests\EmployeeLeaveType;

use App\Models\EmployeeLeaveType;
use Illuminate\Foundation\Http\FormRequest;

class DetachAssignedLeaveTypesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('detachAssignedLeaveTypes', EmployeeLeaveType::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'selectedMorphables' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'selectedMorphables.required' => 'Selected morphables is required',
            'selectedMorphables.array' => 'Selected morphables must be an array',
        ];
    }
}
