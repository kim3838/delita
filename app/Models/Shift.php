<?php

namespace App\Models;

use App\Enums\ShiftHolidayPolicy;
use App\Enums\ShiftType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'holiday_policy',
        'except_holidays',
        'work_start_grace_time',
        'require_lunch_time_in_and_out',
        'lunch_start_grace_time',
        'max_overtime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'code' => 'string',
        'name' => 'string',
        'type' => ShiftType::class,
        'holiday_policy' => ShiftHolidayPolicy::class,
        'except_holidays' => 'array',
        'work_start_grace_time' => 'int',
        'require_lunch_time_in_and_out' => 'boolean',
        'lunch_start_grace_time' => 'int',
        'max_overtime' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)
            ->withTimestamps();
    }
}
