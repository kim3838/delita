<?php

namespace App\Models;

use App\Enums\CreationType;
use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
        'date_registered',
        'creation_type'
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
        'creation_type' => CreationType::class
    ];

    protected $appends = [
        'full_name',
    ];

    protected function fullName(): Attribute
    {
        return Attribute::get(function () {
            return collect([
                $this->family_name,
                $this->given_name,
                $this->middle_name,
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

    public function employmentProfiles(): HasMany
    {
        return $this->hasMany(EmploymentProfile::class);
    }

    public function currentEmploymentProfile(): null | EmploymentProfile
    {
        $now = Carbon::now()
            ->timezone($this->company->timezone ?? config('app.timezone'))
            ->toDateString();

        return $this->hasMany(EmploymentProfile::class)
            ->where('status', EmploymentStatus::ACTIVE->value)
            ->where('start_date', '<=', $now)
            ->where(function($builder)use($now){
                $builder->where(function($closure) use($now){
                    $closure->whereNull('end_date')->orWhere('end_date', '>=', $now);
                });
            })->latest()->first();
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

    /**
     * Shifts assigned to this employee
     *
     * @return BelongsToMany
     * */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class)
            ->using(EmployeeShift::class)
            ->withPivot(['id', 'start_date', 'stated_shift_end_date', 'end_date'])
            ->withTimestamps();
    }

    /**
     * Manager of the employee
     *
     * @return BelongsTo
     * */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Employees managed by this employee
     *
     * @return HasMany
     */
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

    public function groups(): MorphToMany
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveTypes(): BelongsToMany
    {
        return $this->belongsToMany(LeaveType::class)
            ->using(EmployeeLeaveType::class)
            ->withPivot(['id', 'balance_upon_eligibility'])
            ->withTimestamps();
    }
}
