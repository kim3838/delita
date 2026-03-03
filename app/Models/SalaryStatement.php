<?php

namespace App\Models;

use App\Enums\SalaryStatementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryStatement extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_id',

        'type',
        'is_paid',

        'total_days',
        'total_day_offs',
        'total_working_days',
        'total_regular_work_days',
        'total_working_rest_days',
        'total_special_holidays',
        'total_legal_holidays',
        'total_double_holidays',
        'total_full_present',
        'total_present_with_irregularity',
        'total_leave_without_pay',
        'total_leave_with_pay',
        'total_absent',

        'taxable',
        'nontaxable',
        'contribution',
        'withholding_tax',
        'deduction',
        'net',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'ulid' => 'string',
        'payroll_id' => 'int',
        'employee_id' => 'int',

        'type' => SalaryStatementType::class,
        'is_paid' => 'boolean',

        'total_days' => 'int',
        'total_day_offs' => 'int',
        'total_working_days' => 'int',
        'total_regular_work_days' => 'int',
        'total_working_rest_days' => 'int',
        'total_special_holidays' => 'int',
        'total_legal_holidays' => 'int',
        'total_double_holidays' => 'int',
        'total_full_present' => 'int',
        'total_present_with_irregularity' => 'int',
        'total_leave_without_pay' => 'int',
        'total_leave_with_pay' => 'int',
        'total_absent' => 'int',

        'taxable' => 'decimal:6',
        'nontaxable' => 'decimal:6',
        'contribution' => 'decimal:6',
        'withholding_tax' => 'decimal:6',
        'deduction' => 'decimal:6',
        'net' => 'decimal:6',

        'total_basic_gross' => 'decimal:6',
        'total_other_gross' => 'decimal:6',
        'total_taxable' => 'decimal:6',
        'total_nontaxable' => 'decimal:6',
        'total_contribution' => 'decimal:6',
        'total_employer_contribution_share' => 'decimal:6',
        'total_tax_withheld' => 'decimal:6',
        'total_deduction' => 'decimal:6',
        'total_net' => 'decimal:6',

        'total_nonstatutory_benefits' => 'decimal:6',
        'total_13th_month_amount' => 'decimal:6',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryStatementAttendances(): HasMany
    {
        return $this->hasMany(SalaryStatementAttendance::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalaryStatementDetail::class);
    }
}
