<?php

namespace App\Http\Requests\Role;

use App\Models\Role;

class StoreRoleRequest extends BaseRoleRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Role::class);
    }
}
