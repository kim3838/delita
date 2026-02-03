<?php

namespace App\Transformers\PayrollPayload;

use App\Enums\SemiMonthlySequence;
use App\Models\Hydrations\Payroll\PayrollPayload;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(PayrollPayload $payload): array
    {
        $year = $payload->year;
        $month = str_pad($payload->month, 2, '0', STR_PAD_LEFT);
        $payFrequencyLabel = strtoupper($payload->pay_frequency?->label());
        $frequencySequenceFlag = null;

        if($payload->frequency_sequence){
            switch($payload->frequency_sequence){
                case SemiMonthlySequence::FIRST_HALF : $frequencySequenceFlag = 1; break;
                case SemiMonthlySequence::SECOND_HALF : $frequencySequenceFlag = 2; break;
            }
        }

        $startDate = $payload->start->format('Ymd');
        $endDate = $payload->end->format('Ymd');

        return [
            'id' => "{$year}-{$month}-{$payFrequencyLabel}" . ($frequencySequenceFlag ? "-{$frequencySequenceFlag}" : '') . ("-{$startDate}-{$endDate}"),
            'year' => $payload->year,
            'month' => $payload->month,
            'month_readable' => Carbon::createFromDate(null, $payload->month, 1)->format('F'),
            'pay_frequency' => $payload->pay_frequency,
            'pay_frequency_readable' => $payload->pay_frequency?->label(),
            'frequency_sequence' => $payload->frequency_sequence,
            'frequency_sequence_readable' => $payload->frequency_sequence?->label(),
            'start' => $payload->start?->toDateString(),
            'end' => $payload->end?->toDateString(),
            'date_range_readable' => $payload->start->format('F j, Y') . ' - ' . $payload->end->format('F j, Y'),
            'remarks' => ''
        ];
    }
}
