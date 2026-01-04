<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'email',
        'date_registered',
    ];

    protected $casts = [
        'id' => 'int',
        'ulid' => 'string',
        'number' => 'string',
        'email' => 'string',
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
