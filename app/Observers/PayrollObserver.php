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
        $yearLastTwoDigits = substr($year, -2);
        $month = str_pad($payroll->month, 2, '0', STR_PAD_LEFT);
        $frequencySequenceFlag = null;

        if($payroll->frequency_sequence){
            switch($payroll->frequency_sequence){
                case SemiMonthlySequence::FIRST_HALF : $frequencySequenceFlag = 1; break;
                case SemiMonthlySequence::SECOND_HALF : $frequencySequenceFlag = 2; break;
            }
        }

        $prefix = 'PR';

        $number = "{$prefix}{$yearLastTwoDigits}{$month}" . (empty($frequencySequenceFlag) ? '' : "-{$frequencySequenceFlag}");

        $payroll->number = $number;

        return $payroll;
    }
}
