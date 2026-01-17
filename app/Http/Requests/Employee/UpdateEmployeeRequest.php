<?php

namespace App\Http\Requests\Employee;

use App\Enums\DepartmentEmployeeAssignmentType;
use App\Models\DepartmentEmployee;
use App\Models\Employee;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends BaseEmployeeRequest
{
    public function authorize(): bool
    {
        $employee = Employee::query()->findOrfail($this->route('employeeId'));

        return $this->user()->can('update', $employee);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'))
                        ->whereNot('id', $this->route('employeeId'));
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
                            ->whereNot('employee_id', $this->route('employeeId'))
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
