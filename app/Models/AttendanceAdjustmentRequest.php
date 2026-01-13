<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AttendanceAdjustmentRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'attendance_id',
        'first_in',
        'lunch_out',
        'lunch_in',
        'last_out',
    ];

    protected $casts = [
        'requested_by' => 'int',
        'attendance_id' => 'int',
        'first_in' => 'datetime',
        'lunch_out' => 'datetime',
        'lunch_in' => 'datetime',
        'last_out' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function approvalStates(): MorphMany
    {
        return $this->morphMany(RequestApprovalState::class, 'requestable');
    }
}
