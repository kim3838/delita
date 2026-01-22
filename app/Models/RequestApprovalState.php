<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RequestApprovalState extends Model
{
    protected $fillable = [
        'requestable_type',
        'requestable_id',
        'order',
        'approver_id',
        'approved_by',
        'remarks',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'requestable_type' => 'string',
        'requestable_id' => 'int',
        'order' => 'int',
        'approver_id' => 'int',
        'approved_by' => 'int',
        'remarks' => 'string',
        'status' => RequestApprovalStatus::class,
        'approved_at' => 'datetime',

        'requestable_date_requested' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
