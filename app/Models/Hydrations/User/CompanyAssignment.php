<?php

namespace App\Models\Hydrations\User;

use App\Enums\CompanyUserAssignmentType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
class CompanyAssignment extends Model
{
    protected $casts = [
        'user_id' => 'int',
        'user_ulid' => 'string',
        'company_id' => 'int',
        'company_code' => 'string',
        'company_name' => 'string',
        'company_assignment_type' => CompanyUserAssignmentType::class,
        'employee_id' => 'int',
        'employee_number' => 'string',
        'employee_given_name' => 'string',
        'employee_middle_name' => 'string',
        'employee_family_name' => 'string',
    ];

    protected function employeeFullName(): Attribute
    {
        return Attribute::get(function () {
            return collect([
                $this->employee_family_name,
                $this->employee_middle_name,
                $this->employee_given_name,
            ])->filter()->implode(' ');
        });
    }
}
