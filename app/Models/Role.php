<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'account_id',
        'name',
        'guard_name',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
