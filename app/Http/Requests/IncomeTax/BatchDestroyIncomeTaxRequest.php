<?php

namespace App\Http\Requests\IncomeTax;

use App\Models\IncomeTax;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyIncomeTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', IncomeTax::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'income_tax_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'income_tax_ids.required' => 'Income tax ids is required',
            'income_tax_ids.array' => 'Income tax ids must be an array',
        ];
    }
}
