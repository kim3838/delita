<?php

namespace App\Concrete;

use App\Blueprint\PayrollServiceInterface;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\SemiMonthlySequence;
use App\Models\Company;
use App\Models\PayFrequency as PayFrequencyModel;
use App\Traits\HasPayroll;
use App\Traits\HasTime;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollServiceConcrete implements PayrollServiceInterface
{
    protected string $timezone = 'UTC';
    protected Carbon $date;
    protected Collection $payFrequencies;

    use HasTime, HasPayroll;

    public function __construct(
        protected ?Company $company
    ){
        $this->timezone = $company?->timezone ?? 'UTC';
        $this->date = Carbon::now()->timezone($this->timezone);
        $this->payFrequencies = $company?->payFrequencies ?? collect();
    }

    public function setCustomDate(Carbon $date): void
    {
        $this->date = $date;
    }

    public function getLatestWithRecent($payFrequencyEnumValue = null, $recentCount = 1): array
    {
        $recentPayrolls = [];

        $latestPayroll = $this->getPayrollPayload($payFrequencyEnumValue);
        $chainStartDate = $latestPayroll['start']->copy();

        while(count($recentPayrolls) < $recentCount){

            $this->setCustomDate($chainStartDate->subDay());

            $recent = $this->getPayrollPayload($payFrequencyEnumValue);

            $chainStartDate = $recent['start']->copy();

            array_unshift($recentPayrolls, $recent);
        }

        return [
            'recent' => $recentPayrolls,
            'latest' => $latestPayroll,
        ];
    }

    public function getPayrollPayload($payFrequencyEnumValue = null): array
    {
        $debugEnabled = true;

        if(empty($this->company)){
            return [];
        }

        if($debugEnabled){
            $weekly = PayFrequencyEnum::WEEKLY;
            $semimonthly = PayFrequencyEnum::SEMI_MONTHLY;
            $monthly = PayFrequencyEnum::MONTHLY;

            $weeklyFrequency = $this->payFrequencies->where('type', $weekly->value)->first();
            $semimonthlyFrequency = $this->payFrequencies->where('type', $semimonthly->value)->first();
            $monthlyFrequency = $this->payFrequencies->where('type', $monthly->value)->first();

            $latestWeeklyFrequency = $this->getFrequencyDateRange($weeklyFrequency);
            $monthlyFrequencyLatestDateRange = $this->getFrequencyDateRange($monthlyFrequency);
            $semimonthlyFrequencyLatestDateRange = $this->getFrequencyDateRange($semimonthlyFrequency);

            _debug([
                '@currentPayrolls' => [
                    'date' => $this->date->toDateString(),
                    'weekly' => $this->transformPayrollPayload($latestWeeklyFrequency),
                    'monthly' => $this->transformPayrollPayload($monthlyFrequencyLatestDateRange),
                    'semimonthly' => $this->transformPayrollPayload($semimonthlyFrequencyLatestDateRange),
                ],
            ]);
        }

        $payrollPayload = $this->getFrequencyDateRange($this->payFrequencies->where('type', $payFrequencyEnumValue)->first());

        return $payrollPayload;
    }

    private function getFrequencyDateRange(?PayFrequencyModel $frequency): array
    {
        $payrollYear = null;
        $payrollMonth = null;
        $payrollMonthSequence = null;
        $startDate = null;
        $endDate = null;

        if($frequency->type == PayFrequencyEnum::WEEKLY){

            $startDate = $this->getPreviousWeekDay($this->date, $frequency->cut_off_value->value)->addDay();

            if($this->date->dayOfWeek == $frequency->cut_off_value->value){

                $endDate = $this->date;

            } else {
                $endDate = $startDate->copy()->addDays($frequency->days_span - 1);
            }

            $payrollYear = $endDate->year;
            $payrollMonth = $endDate->month;
        }

        if($frequency->type == PayFrequencyEnum::MONTHLY){

            if($frequency->timePeriodPreset->name == 'end_of_month_cut_off'){

                $startDate = $this->date->copy()->startOfMonth();
                $endDate = $this->date->copy()->endOfMonth();
                $payrollYear = $startDate->year;
                $payrollMonth = $startDate->month;
            } else {

                $startDay = (int)collect($frequency->period->cast)->where('key', 'start_date')->first()->value->day;
                $endDay = (int)collect($frequency->period->cast)->where('key', 'end_date')->first()->value->day;

                $startDate = $this->getPreviousNthIncludingCurrent($this->date, $startDay);
                $endDate = $this->getNextNthIncludingCurrent($this->date, $endDay);

                if(in_array($frequency->timePeriodPreset->name, ['05th_cut_off', '10th_cut_off'])){

                    $payrollYear = $startDate->year;
                    $payrollMonth = $startDate->month;
                }

                if(in_array($frequency->timePeriodPreset->name, ['20th_cut_off', '25th_cut_off'])){

                    $payrollYear = $endDate->year;
                    $payrollMonth = $endDate->month;
                }
            }
        }

        if($frequency->type == PayFrequencyEnum::SEMI_MONTHLY){

            if($frequency->timePeriodPreset->name == 'end_of_month_cut_off'){

                $firstHalfStartDay = $this->date->copy()->startOfMonth()->day;
                $firstHalfEndDay = (int)collect($frequency->period->cast)->where('key', '1st_half_end_date')->first()->value->day;
                $secondHalfStartDay = (int)collect($frequency->period->cast)->where('key', '2nd_half_start_date')->first()->value->day;
                $secondHalfEndDay = $this->date->copy()->endOfMonth()->day;

                if($this->date->day >= $secondHalfStartDay){
                    $startDate = $this->date->copy()->day($secondHalfStartDay);
                    $endDate = $this->date->copy()->day($secondHalfEndDay);
                    $payrollMonthSequence = SemiMonthlySequence::SECOND_HALF;
                } else {
                    $startDate = $this->date->copy()->day($firstHalfStartDay);
                    $endDate = $this->date->copy()->day($firstHalfEndDay);
                    $payrollMonthSequence = SemiMonthlySequence::FIRST_HALF;
                }

                $payrollYear = $startDate->year;
                $payrollMonth = $startDate->month;

            } else {

                $firstHalfStartDay = (int)collect($frequency->period->cast)->where('key', '1st_half_start_date')->first()->value->day;
                $firstHalfEndDay = (int)collect($frequency->period->cast)->where('key', '1st_half_end_date')->first()->value->day;
                $secondHalfStartDay = (int)collect($frequency->period->cast)->where('key', '2nd_half_start_date')->first()->value->day;
                $secondHalfEndDay = (int)collect($frequency->period->cast)->where('key', '2nd_half_end_date')->first()->value->day;

                if(in_array($frequency->timePeriodPreset->name, ['05th_cut_off', '10th_cut_off'])){

                    if($this->date->day >= $firstHalfStartDay && $this->date->day <= $firstHalfEndDay){
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $firstHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $firstHalfEndDay);
                        $payrollMonthSequence = SemiMonthlySequence::FIRST_HALF;
                    } else {
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $secondHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $secondHalfEndDay);
                        $payrollMonthSequence = SemiMonthlySequence::SECOND_HALF;
                    }

                    $payrollYear = $startDate->year;
                    $payrollMonth = $startDate->month;
                }

                if(in_array($frequency->timePeriodPreset->name, ['20th_cut_off', '25th_cut_off'])){

                    if($this->date->day >= $secondHalfStartDay && $this->date->day <= $secondHalfEndDay){
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $secondHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $secondHalfEndDay);
                        $payrollMonthSequence = SemiMonthlySequence::SECOND_HALF;
                    } else {
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $firstHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $firstHalfEndDay);
                        $payrollMonthSequence = SemiMonthlySequence::FIRST_HALF;
                    }

                    $payrollYear = $endDate->year;
                    $payrollMonth = $endDate->month;
                }
            }
        }

        return [
            'year' => $payrollYear,
            'month' => $payrollMonth,
            'month_sequence' => $payrollMonthSequence,
            'start' => $startDate,
            'end' => $endDate,
        ];
    }
}
