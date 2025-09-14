<?php

namespace App\Http\Requests\EmployeeShift;

use App\Models\EmployeeShift;
use Illuminate\Foundation\Http\FormRequest;

class DetachAssignedShiftsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('detachAssignedShifts', EmployeeShift::class);
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
