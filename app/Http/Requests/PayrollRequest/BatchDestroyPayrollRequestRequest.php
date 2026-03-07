<?php

namespace App\Http\Requests\PayrollRequest;

use App\Models\PayrollRequest;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyPayrollRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', PayrollRequest::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'payroll_request_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'payroll_request_ids.required' => 'Payroll request ids is required',
            'payroll_request_ids.array' => 'Payroll request ids must be an array',
        ];
    }
}
