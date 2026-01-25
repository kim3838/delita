<?php

namespace App\Http\Requests\OvertimeRequest;

use App\Models\OvertimeRequest;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyOvertimeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', OvertimeRequest::class);
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
