<?php

namespace App\Http\Requests\Department;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Department::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'parent_id' => 'sometimes|required|numeric',
            'name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.required' => 'Parent department is required',
            'name.required' => 'Department name is required',
        ];
    }
}
