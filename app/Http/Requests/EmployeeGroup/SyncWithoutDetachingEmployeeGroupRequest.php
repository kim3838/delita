<?php

namespace App\Http\Requests\EmployeeGroup;

use App\Http\Requests\Group\BaseGroupableRequest;
use App\Models\Group;

class SyncWithoutDetachingEmployeeGroupRequest extends BaseGroupableRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('syncWithoutDetaching', Group::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'employees' => 'required|array',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'employees.required' => 'Employees is required',
        ]);
    }
}
