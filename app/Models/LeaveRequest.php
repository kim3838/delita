<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LeaveRequest extends Model
{
    protected $fillable = [
        'company_id',
        'number',
        'requested_by',
        'date_requested',
        'employee_id',
        'leave_type_id',
        'date_from',
        'date_to',
        'remarks'
    ];

    protected $casts = [
        'company_id' => 'int',
        'number' => 'string',
        'requested_by' => 'int',
        'date_requested' => 'datetime',
        'employee_id' => 'int',
        'leave_type_id' => 'int',
        'date_from' => 'date',
        'date_to' => 'date',
        'remarks' => 'string',
        'status_summary' => RequestApprovalStatus::class
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvalStates(): MorphMany
    {
        return $this->morphMany(RequestApprovalState::class, 'requestable');
    }
}
