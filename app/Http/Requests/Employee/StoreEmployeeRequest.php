<?php

namespace App\Http\Requests\Employee;

use App\Enums\DepartmentEmployeeAssignmentType;
use App\Enums\RegexValidation;
use App\Models\DepartmentEmployee;
use App\Models\Employee;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends BaseEmployeeRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Employee::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'number' => [
                'required',
                'string',
                'regex:' . RegexValidation::NO_WHITESPACE->value,
                'max:255',
                Rule::unique('employees')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],
            'department_assignment_type' => [
                'nullable',
                'numeric',
                'integer',
                function($attribute, $value, $fail) {

                    $departmentId = $this->input('department_id');

                    if(
                        $value == DepartmentEmployeeAssignmentType::HEAD->value &&
                        DepartmentEmployee::query()
                            ->where('department_id', $departmentId)
                            ->where('department_assignment_type', DepartmentEmployeeAssignmentType::HEAD->value)
                            ->exists()
                    ){
                        $fail('Department head already exists');
                    }
                }
            ],
        ]);
    }
}
