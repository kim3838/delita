<?php

namespace App\Http\Requests\EmployeeGroup;

use App\Models\Group;
use App\Models\Hydrations\Group\EmployeeGroup;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = Group::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        $employeeGroup = EmployeeGroup::hydrate([$group->toArray()])->first();

        return $this->user()->can('update', $employeeGroup);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'employees' => 'array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Group name is required',
            'employees.array' => 'Employees must be an array',
        ];
    }
}
