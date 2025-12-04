<?php

namespace App\Http\Requests\EmploymentProfile;

use App\Models\EmploymentProfile;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyEmploymentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', EmploymentProfile::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'employment_profile_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'employment_profile_ids.required' => 'Employment profile ids is required',
            'employment_profile_ids.array' => 'Employment profile ids must be an array',
        ];
    }
}
