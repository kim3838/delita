<?php

namespace App\Models\Hydrations;

use Illuminate\Database\Eloquent\Model;

class SalaryStatementAttendanceTotals extends Model
{
    protected $casts = [
        'company_currency_code' => 'string',
        'regular_pay' => 'decimal:6',
        'night_differential_pay' => 'decimal:6',
        'rest_day_pay' => 'decimal:6',
        'total' => 'decimal:6',
    ];
}
