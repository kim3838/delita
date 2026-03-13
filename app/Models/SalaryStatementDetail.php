<?php

namespace App\Models;

use App\Casts\FormulaComponentType;
use App\Enums\Formulable;
use App\Enums\FormulableComponentSubType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStatementDetail extends Model
{
    protected $fillable = [
        'salary_statement_id',
        'statement_level',
        'formulable_type',
        'component_type',
        'component_sub_type',
        'component_name',

        'component_values',

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
        'salary_statement_id' => 'int',
        'statement_level' => 'boolean',
        'formulable_type' => Formulable::class,
        'component_type' => FormulaComponentType::class,
        'component_sub_type' => FormulableComponentSubType::class,
        'component_name' => 'string',

        'component_values' => 'array',

        'taxable' => 'decimal:6',
        'nontaxable' => 'decimal:6',
        'contribution' => 'decimal:6',
        'withholding_tax' => 'decimal:6',
        'deduction' => 'decimal:6',
        'net' => 'decimal:6',
    ];

    public function salaryStatement(): BelongsTo
    {
        return $this->belongsTo(SalaryStatement::class);
    }
}
