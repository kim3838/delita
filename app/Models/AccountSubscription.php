<?php

namespace App\Models;

use App\Enums\AccountSubscriptionModules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSubscription extends Model
{
    protected $fillable = [
        'account_id',
        'module',
        'date_subscribed'
    ];

    protected $casts = [
        'account_id' => 'int',
        'module' => AccountSubscriptionModules::class,
        'date_subscribed' => 'date:Y-m-d'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
