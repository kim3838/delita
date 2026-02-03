<?php

namespace App\Transformers\PayrollPayload;

use App\Models\Hydrations\Payroll\PayrollPayload;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(PayrollPayload $payload): array
    {
        return [
            'year' => $payload->year,
            'month' => $payload->month,
            'month_readable' => Carbon::createFromDate(null, $payload->month, 1)->format('F'),
            'pay_frequency_readable' => $payload->pay_frequency?->label(),
            'frequency_sequence_readable' => $payload->frequency_sequence?->label(),
            'start' => $payload->start?->toDateString(),
            'end' => $payload->end?->toDateString(),
            'date_range_readable' => $payload->start->format('F j, Y') . ' - ' . $payload->end->format('F j, Y'),
            'remarks' => ''
        ];
    }
}
