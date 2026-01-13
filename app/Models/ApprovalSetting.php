<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalSetting extends Model
{
    protected $fillable = [
        'company_id',
        'request_model'
    ];

    protected $casts = [
        'company_id' => 'int',
        'request_model' => 'string'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function approvers(): HasMany
    {
        return $this->hasMany(ApprovalSettingApprover::class);
    }
}
