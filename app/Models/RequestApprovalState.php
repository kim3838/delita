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
        'status'
    ];

    protected $casts = [
        'requestable_type' => 'string',
        'requestable_id' => 'int',
        'order' => 'int',
        'approver_id' => 'int',
        'status' => RequestApprovalStatus::class
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
}
