<?php

namespace App\Models;

use App\Casts\FormulaComponentType;
use App\Enums\Formulable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SalaryStatementAttendancePayrollComponent extends Model
{
    protected $fillable = [
        'salary_statement_attendance_id',
        'formulable_type',
        'component_type',
        'component_name',
        'regular_pay',
        'night_differential_pay',
        'rest_day_pay',
        'total',
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
        'regular_pay' => 'decimal:6',
        'night_differential_pay' => 'decimal:6',
        'rest_day_pay' => 'decimal:6',
        'total' => 'decimal:6',
    ];

    public function salaryStatementAttendance(): BelongsTo
    {
        return $this->belongsTo(SalaryStatementAttendance::class);
    }
}
