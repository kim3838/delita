<?php

namespace App\Transformers\LeaveType;

use App\Enums\EmploymentType;
use App\Enums\LeaveCarryOverType;
use App\Enums\LeavePeriodType;
use App\Facades\Fractal;
use App\Models\LeaveType;
use App\Transformers\LeaveTypeBalancePerPeriod\ListTransformer as BalancePerPeriodListTransformer;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(LeaveType $model): array
    {
        $limitUsageReadable = $model->limit_usage_value > 0
            ? $model->limit_usage_value . " in " . $model->limit_usage_span_value . " ". $model->limit_usage_span_type?->label() . ($model->limit_usage_span_value > 1 ? 's' : '')
            : 'No limit';

        $periodReadable = '';

        if($model->period_type?->value == LeavePeriodType::INTERVAL->value){
            $periodReadable = $model->period_interval_span_value > 0
                ? $model->period_interval_span_value . " ". $model->period_interval_span_type?->label() . ($model->period_interval_span_value > 1 ? 's' : '')
                : '';
        }

        if($model->period_type?->value == LeavePeriodType::CALENDAR_YEAR->value){
            $periodReadable = $model->period_calendar_span_value > 0
                ? Carbon::create(null, $model->period_calendar_span_value, 1)->format('F')
                : '';
        }

        $eligibilityEmploymentTypesReadable = !empty($model->eligibility_employment_types)
            ? collect($model->eligibility_employment_types)->sort()->values()->map(function($item){
                return EmploymentType::from($item)->label();
            })->toArray()
            : '';

        $eligibilityEmploymentTypesReadable = is_array($eligibilityEmploymentTypesReadable)
            ? implode(', ', $eligibilityEmploymentTypesReadable)
            : $eligibilityEmploymentTypesReadable;

        $carryOverReadable = '';

        if($model->carry_over_balance_per_new_period){

            if($model->carry_over_balance_type?->value == LeaveCarryOverType::ALL->value){
                $carryOverReadable = 'All balance';
            } else if($model->carry_over_balance_type?->value == LeaveCarryOverType::LIMIT->value){
                $carryOverReadable = $model->carry_over_balance_value > 0
                    ? 'Max limit of ' . (int)$model->carry_over_balance_value
                    : '';
            } else if($model->carry_over_balance_type?->value == LeaveCarryOverType::PERCENTAGE->value){
                $carryOverReadable = (float)$model->carry_over_balance_value * 100 . '%';
            }
        } else {
            $carryOverReadable = 'No carry over';
        }

        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'row_number' => $model->row_number,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'type' => $model->type?->toArray(),
            'is_paid' => $model->is_paid,
            'monetizable' => $model->monetizable,

            'limit_usage' => $model->limit_usage,
            'limit_usage_span_type' => $model->limit_usage_span_type?->toArray(),
            'limit_usage_span_value' => $model->limit_usage_span_value,
            'limit_usage_value' => $model->limit_usage_value,
            'limit_usage_value_readable' => $limitUsageReadable,

            'eligibility_employment_types' => $model->eligibility_employment_types,
            'eligibility_employment_types_readable' => $eligibilityEmploymentTypesReadable,

            'period_type' => $model->period_type?->toArray(),
            'period_interval_span_type' => $model->period_interval_span_type?->toArray(),
            'period_interval_span_value' => $model->period_interval_span_value,
            'period_calendar_span_value' => $model->period_calendar_span_value,
            'period_readable' => $periodReadable,

            'initial_balance_upon_eligibility' => $model->initial_balance_upon_eligibility,

            'carry_over_balance_per_new_period' => $model->carry_over_balance_per_new_period,
            'carry_over_balance_type' => $model->carry_over_balance_type?->toArray(),
            'carry_over_balance_value' => $model->carry_over_balance_value,
            'carry_over_readable' => $carryOverReadable,

            'balance_per_period' => Fractal::collection($model->balancePerPeriod, BalancePerPeriodListTransformer::class)['data']
        ];
    }
}
