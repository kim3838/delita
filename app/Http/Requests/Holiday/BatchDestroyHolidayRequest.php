<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Holiday::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'holiday_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'holiday_ids.required' => 'Holiday ids is required',
            'holiday_ids.array' => 'Holiday ids must be an array',
        ];
    }
}
