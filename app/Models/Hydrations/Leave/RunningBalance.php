<?php

namespace App\Models\Hydrations\Leave;

use Illuminate\Database\Eloquent\Model;

class RunningBalance extends Model
{
    protected $casts = [
        'year' => 'string',
        'month' => 'string',
        'year_month' => 'string',
        'employee_id' => 'int',
        'date_start' => 'date',
        'date_series' => 'date',
        'employment_type' => 'int',
        'period_type' => 'int',
        'period_interval_span_type' => 'int',
        'carry_over_balance_per_new_period' => 'boolean',
        'carry_over_balance_type' => 'int',
        'carry_over_balance_value' => 'int',
        'leave_type_id' => 'int',
        'period_span_value' => 'int',
        'eligible' => 'boolean',
        'balance_upon_eligibility' => 'int',
        'eligibility_started' => 'boolean',
        'eligibility_date_start_reference' => 'date',
        'eligibility_date_start' => 'date',
        'sequence_by_period_type' => 'int',
        'period' => 'int',
        'running_balance_additions' => 'int',
        'running_balance_deductions' => 'int',
        'claims' => 'int',
        'period_claims_and_deductions' => 'int',
        'running_balance' => 'int',
    ];
}
