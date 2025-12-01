<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EmployeeLeaveType extends Pivot
{
    protected $table = 'employee_leave_type';

    protected $fillable =[
        'employee_id',
        'leave_type_id',
        'balance_upon_eligibility',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'employee_id' => 'int',
        'leave_type_id' => 'int',
        'balance_upon_eligibility' => 'int',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
