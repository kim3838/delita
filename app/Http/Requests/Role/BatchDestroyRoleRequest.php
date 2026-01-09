<?php

namespace App\Http\Requests\Role;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Role::class);
    }

    public function rules(): array
    {
        return [
            'role_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'role_ids.required' => 'Role ids is required',
            'role_ids.array' => 'Role ids must be an array',
        ];
    }
}
