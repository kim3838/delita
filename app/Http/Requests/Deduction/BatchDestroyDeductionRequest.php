<?php

namespace App\Http\Requests\Deduction;

use App\Models\Deduction;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyDeductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Deduction::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'deduction_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'deduction_ids.required' => 'Deduction ids is required',
            'deduction_ids.array' => 'Deduction ids must be an array',
        ];
    }
}
