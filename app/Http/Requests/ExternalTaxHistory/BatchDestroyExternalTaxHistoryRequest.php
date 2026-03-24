<?php

namespace App\Http\Requests\ExternalTaxHistory;

use App\Models\ExternalTaxHistory;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyExternalTaxHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', ExternalTaxHistory::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'external_tax_history_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'external_tax_history_ids.required' => 'External tax history ids is required',
            'external_tax_history_ids.array' => 'External tax history ids must be an array',
        ];
    }
}
