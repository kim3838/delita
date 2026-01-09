<?php

namespace App\Http\Requests\Role;

use App\Models\Role;

class UpdateRoleRequest extends BaseRoleRequest
{
    public function authorize(): bool
    {
        $role = Role::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        return $this->user()->can('update', $role);
    }
}
