<?php

namespace App\Http\Requests\EmployeeShift;

use App\Models\EmployeeShift;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyEmployeeShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', EmployeeShift::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'shift_assignment_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'shift_assignment_ids.required' => 'Shift assignments is required',
            'shift_assignment_ids.array' => 'Shift assignments must be an array',
        ];
    }
}
