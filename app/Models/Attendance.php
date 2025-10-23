<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'first_in',
        'lunch_out',
        'lunch_in',
        'last_out',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'employee_id' => 'int',
        'shift_id' => 'int',
        'date' => 'date',
        'first_in' => 'datetime',
        'lunch_out' => 'datetime',
        'lunch_in' => 'datetime',
        'last_out' => 'datetime',
        'status' => AttendanceStatus::class
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(AttendanceDetail::class);
    }

    public function overtime(): HasOne
    {
        return $this->hasOne(Overtime::class);
    }
}
