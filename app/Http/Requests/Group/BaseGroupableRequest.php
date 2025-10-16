<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class BaseGroupableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'groups' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'groups.required' => 'Shifts is required',
            'groups.array' => 'Employees must be an array',
        ];
    }
}
