<?php

namespace App\Models;

use App\Enums\HourlyRateType;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\WorkHourType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDetail extends Model
{
    protected $fillable = [
        'attendance_id',
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
        'actual_start',
        'actual_end',
        'grace_before_start_applied',
        'grace_after_start_applied',
        'first_in',
        'lunch_out',
        'lunch_in',
        'last_out',
        'overtime_start',
        'overtime_end',
        'actual_present_start',
        'actual_present_end',
        'actual_present',
        'actual_irregularity_duration_start',
        'actual_irregularity_duration_end',
        'actual_irregularity_duration',
        'late',
        'undertime',
        'flexible_undertime',

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
        'attendance_id' => 'int',
        'date' => 'date',
        'split_type' => ShiftBreakDownSplitType::class,
        'split_start' => 'string',
        'split_end' => 'string',
        'split_duration' => 'int',
        'work_hour_type' => WorkHourType::class,
        'hourly_rate_type' => HourlyRateType::class,
        'regular_rate_multiplier' => 'decimal:6',
        'non_rest_rate_multiplier' => 'decimal:6',
        'hourly_rate_multiplier' => 'decimal:6',
        'base_rate_multiplier' => 'decimal:6',
        'order' => 'int',
        'actual_start' => 'string',
        'actual_end' => 'string',
        'grace_before_start_applied' => 'string',
        'grace_after_start_applied' => 'string',
        'first_in' => 'boolean',
        'lunch_out' => 'boolean',
        'lunch_in' => 'boolean',
        'last_out' => 'boolean',
        'overtime_start' => 'boolean',
        'overtime_end' => 'boolean',
        'actual_present_start' => 'datetime',
        'actual_present_end' => 'datetime',
        'actual_present' => 'int',
        'actual_irregularity_duration_start' => 'datetime',
        'actual_irregularity_duration_end' => 'datetime',
        'actual_irregularity_duration' => 'int',
        'late' => 'int',
        'undertime' => 'int',
        'flexible_undertime' => 'int',

        'regular_pay' => 'decimal:6',
        'night_differential_pay' => 'decimal:6',
        'rest_day_pay' => 'decimal:6',
        'holiday_pay' => 'decimal:6',
        'holiday_pay_forfeited' => 'boolean'
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
