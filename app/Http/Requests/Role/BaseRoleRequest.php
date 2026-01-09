<?php

namespace App\Http\Requests\Role;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class BaseRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_id' => 'required|numeric|exists:accounts,id',
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {

                    $accountId = $this->input('account_id');

                    $queryBuilder = Role::query()->getQuery()
                        ->when($this->route('ulid') ?? false, function ($query, $value) {
                            $query->where('ulid', '!=', $value);
                        })
                        ->where('account_id', $accountId)
                        ->where('name', $value);

                    if ($queryBuilder->exists()) {
                        $fail('Role name already exists');
                    }
                },
            ],
            'permission_ids' => 'array'
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.exists' => 'Account not found',
            'account_id.required' => 'Account is required',
            'account_id.numeric' => 'Account id must be numeric',
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name must be less than 255 characters',
            'permission_ids.array' => 'Permission ids must be an array',
        ];
    }
}
