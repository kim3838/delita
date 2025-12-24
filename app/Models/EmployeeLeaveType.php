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
        'override_balance_upon_eligibility',
        'balance_upon_eligibility',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'employee_id' => 'int',
        'leave_type_id' => 'int',
        'override_balance_upon_eligibility' => 'boolean',
        'balance_upon_eligibility' => 'decimal:1',
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
