<?php

namespace App\Http\Requests\Shift;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Shift::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'shift_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'shift_ids.required' => 'Shift ids is required',
            'shift_ids.array' => 'Shift ids must be an array',
        ];
    }
}
