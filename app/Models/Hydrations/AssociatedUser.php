<?php

namespace App\Models\Hydrations;

use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Model;

class AssociatedUser extends Model
{
    protected $casts = [
        'user_id' => 'int',
        'user_ulid' => 'string',
        'user_username' => 'string',
        'user_email' => 'string',
        'user_status' => UserStatus::class,
        'user_email_verified_at' => 'datetime:Y-m-d H:i:s',
        'user_timezone' => 'string',
    ];
}
