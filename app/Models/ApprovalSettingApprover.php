<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalSettingApprover extends Model
{
    protected $fillable = [
        'approval_setting_id',
        'order',
        'approver_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function approvalSetting(): BelongsTo
    {
        return $this->belongsTo(ApprovalSetting::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
