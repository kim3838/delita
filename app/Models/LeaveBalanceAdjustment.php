<?php

namespace App\Models;

use App\Enums\LeaveBalanceAdjustmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalanceAdjustment extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'type',
        'effective_date',
        'balance',
        'remarks'
    ];

    protected $casts = [
        'employee_id' => 'int',
        'leave_type_id' => 'int',
        'type' => LeaveBalanceAdjustmentType::class,
        'balance' => 'decimal:1',
        'effective_date' => 'date',
        'remarks' => 'string'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
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
