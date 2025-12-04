<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class BaseGroupableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'group_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'group_ids.required' => 'Group ids is required',
            'group_ids.array' => 'Group ids must be an array',
        ];
    }
}
