<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PayrollRequest extends Model
{
    protected $fillable = [
        'company_id',
        'number',
        'requested_by',
        'date_requested',
        'payroll_id',
        'remarks'
    ];

    protected $casts = [
        'company_id' => 'int',
        'number' => 'string',
        'requested_by' => 'int',
        'date_requested' => 'datetime',
        'payroll_id' => 'int',
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

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function approvalStates(): MorphMany
    {
        return $this->morphMany(RequestApprovalState::class, 'requestable');
    }
}
