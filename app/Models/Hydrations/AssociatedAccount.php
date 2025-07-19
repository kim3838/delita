<?php

namespace App\Models\Hydrations;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;

class AssociatedAccount extends Model
{
    protected $casts = [
        'account_id' => 'int',
        'account_ulid' => 'string',
        'account_number' => 'string',
        'account_type' => AccountType::class
    ];
}
