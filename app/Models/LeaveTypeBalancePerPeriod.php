<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveTypeBalancePerPeriod extends Model
{
    protected $fillable = [
        'leave_type_id',
        'from_period',
        'to_period',
        'balance'
    ];

    protected $casts = [
        'id' => 'int',
        'leave_type_id' => 'int',
        'from_period' => 'int',
        'to_period' => 'int',
        'balance' => 'decimal:1',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
