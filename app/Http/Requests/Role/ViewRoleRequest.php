<?php

namespace App\Http\Requests\Role;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class ViewRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = Role::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        return $this->user()->can('view', $role);
    }
}
