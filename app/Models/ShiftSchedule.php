<?php

namespace App\Models;

use App\Enums\WeekDay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSchedule extends Model
{
    protected $fillable = [
        'shift_id',
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
        'shift_id' => 'int',
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

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
