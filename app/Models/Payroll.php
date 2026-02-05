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
