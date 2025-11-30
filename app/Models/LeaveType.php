<?php

namespace App\Models;

use App\Enums\LeaveCarryOverType;
use App\Enums\LeaveIntervalSpanType;
use App\Enums\LeavePeriodType;
use App\Enums\LeaveType as LeaveTypeEnum;
use App\Enums\LeaveUsageSpanType;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'is_paid',
        'monetizable',

        //Limit usage
        'limit_usage',
        'limit_usage_span_type',
        'limit_usage_span_value',
        'limit_usage_value',

        //Eligibility
        'eligibility_employment_types',

        //Period type
        'period_type',
        //Interval period type
        'period_interval_span_type',
        'period_interval_span_value',
        //Calendar year period type
        'period_calendar_span_value',

        //Eligibility balance
        'initial_balance_upon_eligibility',

        //Carry over balance per new period
        'carry_over_balance_per_new_period',
        'carry_over_balance_type',
        'carry_over_balance_value',
    ];

    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'code' => 'string',
        'name' => 'string',
        'type' => LeaveTypeEnum::class,
        'is_paid' => 'boolean',
        'monetizable' => 'boolean',

        'limit_usage' => 'boolean',
        'limit_usage_span_type' => LeaveUsageSpanType::class,
        'limit_usage_span_value' => 'int',
        'limit_usage_value' => 'int',

        'eligibility_employment_types' => 'array',

        'period_type' => LeavePeriodType::class,
        'period_interval_span_type' => LeaveIntervalSpanType::class,
        'period_interval_span_value' => 'int',
        'period_calendar_span_value' => 'int',

        'initial_balance_upon_eligibility' => 'int',

        'carry_over_balance_per_new_period' => 'boolean',
        'carry_over_balance_type' => LeaveCarryOverType::class,
        'carry_over_balance_value' => 'int',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function balancePerPeriod()
    {
        return $this->hasMany(LeaveTypeBalancePerPeriod::class);
    }
}
