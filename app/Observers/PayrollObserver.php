<?php

namespace App\Observers;

use App\Enums\SemiMonthlySequence;
use App\Models\Payroll;
use Illuminate\Support\Str;

class PayrollObserver
{
    public function creating(Payroll $payroll): bool
    {
        if (empty($payroll->ulid)) {
            $payroll->ulid = (string) Str::ulid();
        }

        $this->addCustomNumberAttribute($payroll);

        return true;
    }

    public function addCustomNumberAttribute(Payroll $payroll): Payroll
    {
        $year = $payroll->year;
        $month = str_pad($payroll->month, 2, '0', STR_PAD_LEFT);
        $payFrequencyLabel = strtoupper($payroll->pay_frequency?->label());
        $frequencySequenceFlag = null;

        if($payroll->frequency_sequence){
            switch($payroll->frequency_sequence->type){
                case SemiMonthlySequence::FIRST_HALF : $frequencySequenceFlag = 1; break;
                case SemiMonthlySequence::SECOND_HALF : $frequencySequenceFlag = 2; break;
            }
        }

        $startDate = $payroll->start_date->format('Ymd');
        $endDate = $payroll->end_date->format('Ymd');

        $prefix = 'PAYROLL';

        $number = "{$prefix}-{$year}-{$month}-{$payFrequencyLabel}" . ($frequencySequenceFlag ? "-{$frequencySequenceFlag}" : '') . ("-{$startDate}-{$endDate}");

        $payroll->number = $number;

        return $payroll;
    }
}
