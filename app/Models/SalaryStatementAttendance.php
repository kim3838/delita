<?php

namespace App\Models;

use App\Enums\SalaryStatementAttendanceDayType;
use App\Enums\SalaryStatementAttendanceStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Carbon date
 * @property SalaryStatementAttendanceStatus status
 * @property SalaryStatementAttendanceDayType day_type
 **/
class SalaryStatementAttendance extends Model
{
    protected $fillable = [
        'salary_statement_id',
        'attendance_id',
        'date',
        'status',
        'day_type',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'ulid' => 'string',
        'salary_statement_id' => 'int',
        'date' => 'date',
        'status' => SalaryStatementAttendanceStatus::class,
        'day_type' => SalaryStatementAttendanceDayType::class,
    ];

    public function salaryStatement(): BelongsTo
    {
        return $this->belongsTo(SalaryStatement::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalaryStatementAttendanceDetail::class);
    }

    public function payrollComponents(): HasMany
    {
        return $this->hasMany(SalaryStatementAttendancePayrollComponent::class);
    }
}
