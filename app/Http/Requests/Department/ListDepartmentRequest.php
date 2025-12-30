<?php

namespace App\Http\Requests\Department;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class ListDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Department::class);
    }
}
