<?php

namespace App\Models\Hydrations;

use App\Enums\AccountPlan;
use Illuminate\Database\Eloquent\Model;

class AssociatedAccount extends Model
{
    protected $casts = [
        'id' => 'int',
        'ulid' => 'string',
        'number' => 'string',
        'plan' => AccountPlan::class,
    ];
}
