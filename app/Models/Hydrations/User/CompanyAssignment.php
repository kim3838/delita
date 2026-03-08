<?php

namespace App\Models\Hydrations\User;

use App\Enums\CompanyUserAssignmentType;
use Illuminate\Database\Eloquent\Model;

class CompanyAssignment extends Model
{
    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'user_ulid' => 'string',
        'company_id' => 'int',
        'company_code' => 'string',
        'company_name' => 'string',
        'company_assignment_type' => CompanyUserAssignmentType::class,
        'employee_id' => 'int',
        'employee_number' => 'string',
        'employee_full_name' => 'string',
    ];
}
