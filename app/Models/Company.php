<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Nnjeim\World\Models\Country;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'country_id',
        'code',
        'name',
        'currency',
        'timezone',
    ];

    protected $casts = [
        'id' => 'int',
        'ulid' => 'string',
        'account_id' => 'int',
        'country_id' => 'int',
        'code' => 'string',
        'name' => 'string',
        'currency' => 'string',
        'timezone' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
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
            ->withPivot(['id', 'settings'])
            ->withTimestamps();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(Compensation::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(Deduction::class);
    }

    public function incomeTaxes(): HasMany
    {
        return $this->hasMany(IncomeTax::class);
    }

    public function payPeriodSetting(): HasOne
    {
        return $this->hasOne(PayPeriodSetting::class);
    }

    public function salaryStatementModules(): HasMany
    {
        return $this->hasMany(SalaryStatementModule::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
