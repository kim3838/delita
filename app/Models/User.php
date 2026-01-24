<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Traits\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;

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
        'status',
        'timezone',
        'created_by',
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
            'ulid' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
            //Todo: If theres no company left assigned to a user, mark status as inactive
            'status' => UserStatus::class,
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('assignment_type')
            ->withTimestamps();
    }

    public function twoFactorEnabled(): Attribute
    {
        return Attribute::get(fn() => !is_null($this->two_factor_secret));
    }

    public function twoFactorConfirmed(): Attribute
    {
        return Attribute::get(fn() => !is_null($this->two_factor_confirmed_at));
    }

    public function isSuperAdmin(): bool
    {
        return $this->type == UserType::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->type == UserType::ADMIN;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
