<?php

namespace App\Transformers\Payroll;

use App\Models\Payroll;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(Payroll $payroll): array
    {
        return [
            'id' => $payroll->id,
            'ulid' => $payroll->ulid,
            'company_id' => $payroll->company_id,
            'number' => $payroll->number,
            'year' => $payroll->year,
            'month' => $payroll->month,
            'month_readable' => Carbon::createFromDate(null, $payroll->month, 1)->format('F'),
            'pay_frequency' => $payroll->pay_frequency?->toArray(),
            'frequency_sequence' => $payroll->frequency_sequence?->toArray(),
            'start_date' => $payroll->start_date?->toDateString(),
            'end_date' => $payroll->end_date?->toDateString(),
            'remarks' => $payroll->remarks,
            'status' => $payroll->status?->toArray(),

            'date_range_readable' => $payroll->start_date->format('M j, Y') . ' - ' . $payroll->end_date->format('M j, Y'),
        ];
    }
}
