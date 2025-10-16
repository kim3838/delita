<?php

namespace App\Http\Requests\EmployeeGroup;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Group::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'employees' => 'array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Group name is required',
            'employees.array' => 'Employees must be an array',
        ];
    }
}
