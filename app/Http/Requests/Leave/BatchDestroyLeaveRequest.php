<?php

namespace App\Http\Requests\Leave;

use App\Models\Leave;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Leave::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'leave_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'leave_ids.required' => 'Leave ids is required',
            'leave_ids.array' => 'Leave ids must be an array',
        ];
    }
}
