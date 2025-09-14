<?php

namespace App\Models\Hydrations\Employee;

use Illuminate\Database\Eloquent\Model;

class ShiftAssignment extends Model
{
    protected $casts = [
        'row_number' => 'int',
        'id' => 'int',
        'employee_shift_id' => 'int',
        'employee_id' => 'int',
        'shift_id' => 'int',

        'employee_number' => 'string',

        'shift_ulid' => 'string',
        'shift_code' => 'string',
        'shift_name' => 'string',
        'shift_start_date' => 'date',
        'shift_stated_shift_end_date' => 'boolean',
        'shift_end_date' => 'date',
    ];
}
