<?php

namespace App\Http\Requests\Role;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class ListRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Role::class);
    }
}
