<?php

namespace App\Http\Requests\PayrollInquiry;

use Illuminate\Foundation\Http\FormRequest;

class PayrollInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'recent_count' => 'sometimes|integer|min:0|max:48'
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'company_id.exists' => 'Company not found',
            'recent_count.integer' => 'Recent count must be an integer',
            'recent_count.max' => 'Recent count must be 48 or less'
        ];
    }
}
