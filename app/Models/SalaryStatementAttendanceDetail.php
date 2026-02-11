<?php

namespace App\Models;

use App\Enums\HourlyRateType;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\WorkHourType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStatementAttendanceDetail extends Model
{
    protected $fillable = [
        'salary_statement_attendance_id',
        'date',
        'split_type',
        'split_start',
        'split_end',
        'split_duration',

        'work_hour_type',
        'hourly_rate_type',
        'regular_rate_multiplier',
        'non_rest_rate_multiplier',
        'hourly_rate_multiplier',
        'base_rate_multiplier',
        'order',

        'regular_pay',
        'night_differential_pay',
        'rest_day_pay',
        'holiday_pay',
        'holiday_pay_forfeited',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'salary_statement_attendance_id' => 'int',

        'split_type' => ShiftBreakDownSplitType::class,
        'split_start' => 'string',
        'split_end' => 'string',
        'split_duration' => 'int',

        'work_hour_type' => WorkHourType::class,
        'hourly_rate_type' => HourlyRateType::class,
        'hourly_rate_multiplier' => 'decimal:6',
        'base_rate_multiplier' => 'decimal:6',
        'order' => 'int',

        'regular_pay' => 'decimal:6',
        'night_differential_pay' => 'decimal:6',
        'rest_day_pay' => 'decimal:6',
        'holiday_pay' => 'decimal:6',
        'holiday_pay_forfeited' => 'boolean'
    ];

    public function salaryStatementAttendance(): BelongsTo
    {
        return $this->belongsTo(SalaryStatementAttendance::class);
    }
}
