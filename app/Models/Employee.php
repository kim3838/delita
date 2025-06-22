<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'number',
        'given_name',
        'middle_name',
        'family_name',
        'birth_date',
        'gender',
        'marital_status',
        'date_registered'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'int',
        'number' => 'string',
        'gender' => Gender::class,
        'marital_status' => MaritalStatus::class,
        'birth_date' => 'date',
    ];

    protected $appends = [
        'full_name',
    ];

    protected function fullName(): Attribute
    {
        return Attribute::get(function () {
            return collect([
                $this->given_name,
                $this->middle_name,
                $this->family_name,
            ])->filter()->implode(' ');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
