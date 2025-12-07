<?php

namespace App\Models\Hydrations\Employee;

use Illuminate\Database\Eloquent\Model;

class LeaveTypeAssignment extends Model
{
    protected $casts = [
        'row_number' => 'int',
        'id' => 'int',
        'employee_leave_type_id' => 'int',
        'employee_id' => 'int',
        'leave_type_id' => 'int',

        'employee_number' => 'string',

        'leave_type_ulid' => 'string',
        'leave_type_code' => 'string',
        'leave_type_name' => 'string',
        'leave_type_initial_balance_upon_eligibility' => 'int',

        'leave_type_assignment_override_balance_upon_eligibility' => 'boolean',
        'leave_type_assignment_balance_upon_eligibility' => 'int',
    ];
}
