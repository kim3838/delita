<?php

namespace App\Models;

use App\Enums\ShiftType;
use App\Enums\WeekDay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceShiftDetail extends Model
{
    protected $fillable = [
        'attendance_id',
        /**
         * Shift Assignment
         **/
        'start_date',
        'stated_shift_end_date',
        'end_date',

        /**
         * Shift
         **/
        'code',
        'name',
        'type',
        'work_start_grace_time',
        'require_lunch_time_in_and_out',
        'lunch_start_grace_time',
        'max_overtime',

        /**
         * Shift Schedule
         **/
        'week_day',
        'is_rest_day',
        'is_day_off',
        'timezone',
        'is_flexible',
        'work_start',
        'work_end',
        'total_work_hours_with_breaks',
        'has_lunch_break',
        'lunch_break_start',
        'lunch_break_end',
        'total_lunch_break_hours',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'attendance_id' => 'int',

        /**
         * Shift Assignment
         **/
        'start_date' => 'date',
        'stated_shift_end_date' => 'boolean',
        'end_date' => 'date',

        /**
         * Shift
         **/
        'code' => 'string',
        'name' => 'string',
        'type' => ShiftType::class,
        'work_start_grace_time' => 'int',
        'require_lunch_time_in_and_out' => 'boolean',
        'lunch_start_grace_time' => 'int',
        'max_overtime' => 'decimal:2',

        /**
         * Shift Schedule
         **/
        'week_day' => WeekDay::class,
        'is_rest_day' => 'boolean',
        'is_day_off' => 'boolean',
        'is_flexible' => 'boolean',
        'timezone' => 'string',
        'work_start' => 'string',
        'work_end' => 'string',
        'total_work_hours_with_breaks' => 'string',
        'has_lunch_break' => 'boolean',
        'lunch_break_start' => 'string',
        'lunch_break_end' => 'string',
        'total_lunch_break_hours' => 'string',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
