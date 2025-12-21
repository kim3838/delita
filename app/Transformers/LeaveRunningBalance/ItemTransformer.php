<?php

namespace App\Transformers\LeaveRunningBalance;

use App\Models\Hydrations\Leave\RunningBalance;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(RunningBalance $runningBalance): array
    {
        return [
            'year' => $runningBalance->year,
            'month' => $runningBalance->month,
            'year_month' => $runningBalance->year_month,
            'employee_id' => $runningBalance->employee_id,
            'date_start' => $runningBalance->date_start?->toDateString(),
            'date_series' => $runningBalance->date_series?->toDateString(),
            'employment_type' => $runningBalance->employment_type,
            'period_type' => $runningBalance->period_type,
            'period_interval_span_type' => $runningBalance->period_interval_span_type,
            'carry_over_balance_per_new_period' => $runningBalance->carry_over_balance_per_new_period,
            'carry_over_balance_type' => $runningBalance->carry_over_balance_type,
            'carry_over_balance_value' => $runningBalance->carry_over_balance_value,
            'leave_type_id' => $runningBalance->leave_type_id,
            'period_span_value' => $runningBalance->period_span_value,
            'eligible' => $runningBalance->eligible,
            'balance_upon_eligibility' => $runningBalance->balance_upon_eligibility,
            'eligibility_started' => $runningBalance->eligibility_started,
            'eligibility_date_start_reference' => $runningBalance->eligibility_date_start_reference?->toDateString(),
            'eligibility_date_start' => $runningBalance->eligibility_date_start?->toDateString(),
            'sequence_by_period_type' => $runningBalance->sequence_by_period_type,
            'period' => $runningBalance->period,
            'running_balance_additions' => $runningBalance->running_balance_additions,
            'running_balance_deductions' => $runningBalance->running_balance_deductions,
            'claims' => $runningBalance->claims,
            'period_claims_and_deductions' => $runningBalance->period_claims_and_deductions,
            'running_balance' => $runningBalance->running_balance,
        ];
    }
}
