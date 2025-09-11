<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EmployeeShift extends Pivot
{
    protected $fillable =[
        'employee_id',
        'shift_id',
        'start_date',
        'stated_shift_end_date',
        'end_date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'employee_id' => 'int',
        'shift_id' => 'int',
        'start_date' => 'date',
        'stated_shift_end_date' => 'boolean',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
