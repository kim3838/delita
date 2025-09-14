<?php

namespace App\Models\Hydrations\Employee;

use Illuminate\Database\Eloquent\Model;

class ShiftsByEmployees extends Model
{
    protected $casts = [
        'row_number' => 'int',
        'id' => 'int',
        'employee_id' => 'int',
        'assigned_shift_codes' => 'string',
    ];
}
