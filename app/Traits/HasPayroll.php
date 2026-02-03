<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasPayroll
{
    public function transformPayrollPayload($payload): array
    {
        return [
            'year' => $payload['year'],
            'month' => $payload['month'],
            'month_readable' => Carbon::createFromDate(null, $payload['month'], 1)->format('F'),
            'pay_frequency' => $payload['pay_frequency'],
            'pay_frequency_readable' => $payload['pay_frequency']?->label(),
            'frequency_sequence' => $payload['frequency_sequence'],
            'frequency_sequence_readable' => $payload['frequency_sequence']?->label(),
            'start' => $payload['start']?->toDateString(),
            'end' => $payload['end']?->toDateString(),
            'date_range_readable' => $payload['start']->format('F j, Y') . ' - ' . $payload['end']->format('F j, Y'),
        ];
    }
}
