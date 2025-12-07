<?php

namespace App\Models\Hydrations\Employee;

use Illuminate\Database\Eloquent\Model;

class LeaveTypesByEmployees extends Model
{
    protected $casts = [
        'row_number' => 'int',
        'id' => 'int',
        'employee_id' => 'int',
        'assigned_leave_type_codes' => 'string',
    ];
}
