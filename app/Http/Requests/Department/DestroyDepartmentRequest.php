<?php

namespace App\Http\Requests\Department;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class DestroyDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $department = Department::query()->findOrfail($this->route('departmentId'));

        return $this->user()->can('delete', $department);
    }
}
