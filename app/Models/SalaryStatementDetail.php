<?php

namespace App\Models;

use App\Casts\FormulaComponentType;
use App\Enums\Formulable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStatementDetail extends Model
{
    protected $fillable = [
        'salary_statement_id',
        'formulable_type',
        'component_type',
        'component_name',

        'component_values',

        'regular_pay',
        'night_differential_pay',
        'rest_day_pay',

        'taxable',
        'nontaxable',
        'deduction',
        'contribution',
        'withholding_tax',
        'net',
        'employer_contribution',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'salary_statement_attendance_id' => 'int',
        'formulable_type' => Formulable::class,
        'component_type' => FormulaComponentType::class,
        'component_name' => 'string',

        'component_values' => 'array',

        'regular_pay' => 'decimal:6',
        'night_differential_pay' => 'decimal:6',
        'rest_day_pay' => 'decimal:6',

        'taxable' => 'decimal:6',
        'nontaxable' => 'decimal:6',
        'deduction' => 'decimal:6',
        'contribution' => 'decimal:6',
        'withholding_tax' => 'decimal:6',
        'net' => 'decimal:6',
        'employer_contribution' => 'decimal:6',
    ];

    public function salaryStatement(): BelongsTo
    {
        return $this->belongsTo(SalaryStatement::class);
    }
}
