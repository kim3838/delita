<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'code',
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('assignment_type')
            ->withTimestamps();
    }

    public function formulas()
    {
        return $this->belongsToMany(Formula::class)
            ->withPivot('settings')
            ->withTimestamps();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
