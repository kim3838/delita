<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'company_id',
        'number',
        'requested_by',
        'date_requested',
        'attendance_id',
        'start',
        'end',
        'duration',
        'remarks'
    ];

    protected $casts = [
        'company_id' => 'int',
        'number' => 'string',
        'requested_by' => 'int',
        'date_requested' => 'datetime',
        'attendance_id' => 'int',
        'start' => 'datetime',
        'end' => 'datetime',
        'duration' => 'int',
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

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvalStates(): MorphMany
    {
        return $this->morphMany(RequestApprovalState::class, 'requestable');
    }
}
