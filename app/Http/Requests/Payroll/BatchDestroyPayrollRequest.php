<?php

namespace App\Http\Requests\Payroll;

use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyPayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Payroll::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'payroll_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'payroll_ids.required' => 'Payroll ids is required',
            'payroll_ids.array' => 'Payroll ids must be an array',
        ];
    }
}
