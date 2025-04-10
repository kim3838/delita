<?php

namespace App\Models;

use App\Enums\UserType;
use App\Traits\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'two_factor_enabled',
        'two_factor_confirmed'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime:Y-m-d H:i:s',
            'password' => 'hashed',
            'type' => UserType::class,
        ];
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('assignment_type')
            ->withTimestamps();
    }

    public function twoFactorEnabled(): Attribute
    {
        return new Attribute(
            get: fn() => !is_null($this->two_factor_secret),
        );
    }

    public function twoFactorConfirmed(): Attribute
    {
        return new Attribute(
            get: fn() => !is_null($this->two_factor_confirmed_at),
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->type == UserType::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->type == UserType::ADMIN;
    }
}
