<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'type',
        'date_registered',
    ];

    protected $casts = [
        'id' => 'int',
        'ulid' => 'string',
        'number' => 'string',
        'type' => AccountType::class,
        'date_registered' => 'date:Y-m-d'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(AccountSubscription::class);
    }
}
