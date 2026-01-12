<?php

namespace App\Http\Requests\Compensation;

use App\Models\Compensation;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyCompensationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Compensation::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'compensation_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'compensation_ids.required' => 'Compensation ids is required',
            'compensation_ids.array' => 'Compensation ids must be an array',
        ];
    }
}
