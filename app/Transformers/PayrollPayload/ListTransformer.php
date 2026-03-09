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
        $monthReadable = Carbon::createFromDate(null, $payload->month, 1)->format('F');
        $payFrequencyLabel = $payload->pay_frequency?->label();
        $payFrequencyLabelUppercase = strtoupper($payload->pay_frequency?->label());
        $frequencySequenceReadable = null;
        $frequencySequenceFlag = null;

        if($payload->frequency_sequence){
            switch($payload->frequency_sequence){
                case SemiMonthlySequence::FIRST_HALF :
                    $frequencySequenceReadable = SemiMonthlySequence::FIRST_HALF->label();
                    $frequencySequenceFlag = 1;
                    break;
                case SemiMonthlySequence::SECOND_HALF :
                    $frequencySequenceReadable = SemiMonthlySequence::SECOND_HALF->label();
                    $frequencySequenceFlag = 2;
                    break;
            }
        }

        $startDate = $payload->start->format('Ymd');
        $endDate = $payload->end->format('Ymd');
        $dateRangeReadable = $payload->start->format('M j, Y') . ' - ' . $payload->end->format('M j, Y');

        return [
            'id' => "{$year}-{$month}-{$payFrequencyLabelUppercase}" . ($frequencySequenceFlag ? "-{$frequencySequenceFlag}" : '') . ("-{$startDate}-{$endDate}"),
            'summary' => "{$payFrequencyLabel} - {$year} {$monthReadable} " . ($frequencySequenceReadable ? "({$frequencySequenceReadable}) " : ''),
            'year' => $payload->year,
            'month' => $payload->month,
            'month_readable' => $monthReadable,
            'pay_frequency' => $payload->pay_frequency?->toArray(),
            'pay_frequency_readable' => $payload->pay_frequency?->label(),
            'frequency_sequence' => $payload->frequency_sequence?->toArray(),
            'frequency_sequence_readable' => $payload->frequency_sequence?->label(),
            'start' => $payload->start?->toDateString(),
            'start_readable' => $payload->start?->format('M j, Y'),
            'end' => $payload->end?->toDateString(),
            'end_readable' => $payload->end?->format('M j, Y'),
            'date_range_readable' => $dateRangeReadable,
            'remarks' => $payload->remarks ?? '',
            'payroll' => $payload->payroll,
        ];
    }
}
