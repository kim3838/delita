<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStatement extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_id',
        'gross',
        'deduction',
        'taxable_income',
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
        'gross' => 'decimal:6',
        'deduction' => 'decimal:6',
        'taxable_income' => 'decimal:6',
        'net' => 'decimal:6',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
