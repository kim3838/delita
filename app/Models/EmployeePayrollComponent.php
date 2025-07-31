<?php

namespace App\Models;

use App\Enums\PayFrequency;
use App\Enums\PayPeriod;
use App\Enums\PayType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmployeePayrollComponent extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_componentable_id',
        'payroll_componentable_type',
        'formulable_type',
        'amount',
        'currency',
        'pay_period',
        'pay_type',
        'pay_frequency',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'employee_id' => 'int',
        'payroll_componentable_id' => 'int',
        'payroll_componentable_type' => 'string',
        'formulable_type' => Formulable::class,
        'amount' => 'decimal:4',
        'currency' => 'string',
        'pay_period' => PayPeriod::class,
        'pay_type' => PayType::class,
        'pay_frequency' => PayFrequency::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function payrollComponentable(): MorphTo
    {
        return $this->morphTo();
    }
}
