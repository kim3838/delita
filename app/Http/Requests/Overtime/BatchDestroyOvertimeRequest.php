<?php

namespace App\Http\Requests\Overtime;

use App\Models\Overtime;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Overtime::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'overtime_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'overtime_ids.required' => 'Overtime is required',
            'overtime_ids.array' => 'Overtime must be an array',
        ];
    }
}
