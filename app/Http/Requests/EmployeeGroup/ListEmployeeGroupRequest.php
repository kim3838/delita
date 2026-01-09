<?php

namespace App\Http\Requests\EmployeeGroup;

use App\Models\Hydrations\Group\EmployeeGroup;
use Illuminate\Foundation\Http\FormRequest;

class ListEmployeeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', EmployeeGroup::class);
    }
}
