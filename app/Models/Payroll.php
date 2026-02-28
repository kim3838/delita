<?php

namespace App\Models;

use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\PayrollStatus;
use App\Enums\SemiMonthlySequence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    protected $fillable = [
        'company_id',
        'number',
        'year',
        'month',
        'pay_frequency',
        'frequency_sequence',
        'start_date',
        'end_date',
        'remarks',
        'status'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'company_id' => 'int',
        'number' => 'string',
        'year' => 'int',
        'month' => 'int',
        'pay_frequency' => PayFrequencyEnum::class,
        'frequency_sequence' => SemiMonthlySequence::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => PayrollStatus::class,

        'total_basic_gross' => 'decimal:6',
        'total_other_gross' => 'decimal:6',
        'total_taxable' => 'decimal:6',
        'total_nontaxable' => 'decimal:6',
        'total_contribution' => 'decimal:6',
        'total_employer_contribution_share' => 'decimal:6',
        'total_tax_withheld' => 'decimal:6',
        'total_deduction' => 'decimal:6',
        'total_net' => 'decimal:6',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function salaryStatements(): HasMany
    {
        return $this->hasMany(SalaryStatement::class);
    }
}
