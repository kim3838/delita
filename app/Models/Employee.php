<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'designation_id',
        'manager_id',
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
        'ulid' => 'string',
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
                $this->family_name,
                $this->middle_name,
                $this->given_name,
            ])->filter()->implode(' ');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(EmployeeContact::class, 'employee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    // Manager of the employee
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    // Employees managed by this employee
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function payrollComponents(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class);
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class)
            ->where('payroll_componentable_type', 'compensation');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class)
            ->where('payroll_componentable_type', 'deduction');
    }

    public function incomeTaxes(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class)
            ->where('payroll_componentable_type', 'income_tax');
    }
}
