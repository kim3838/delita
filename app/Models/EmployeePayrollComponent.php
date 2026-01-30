<?php

namespace App\Models;

use App\Enums\AmountablePayrollComponentEnd;
use App\Enums\Formulable;
use App\Enums\PayPeriod;
use App\Enums\AmountablePayrollComponentStart;
use App\Enums\PayType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmployeePayrollComponent extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_componentable_type',
        'payroll_componentable_id',
        'formulable_type',
        'amount',
        'currency',
        'pay_period',
        'pay_type',
        'amountable_start',
        'start_date',
        'amountable_end',
        'end_date',
    ];

    protected $casts = [
        'employee_id' => 'int',
        'payroll_componentable_type' => 'string',
        'payroll_componentable_id' => 'int',
        'formulable_type' => Formulable::class,
        'amount' => 'decimal:6',
        'currency' => 'string',
        'pay_period' => PayPeriod::class,
        'pay_type' => PayType::class,
        'amountable_start' => AmountablePayrollComponentStart::class,
        'start_date' => 'date',
        'amountable_end' => AmountablePayrollComponentEnd::class,
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
