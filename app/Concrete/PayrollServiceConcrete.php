<?php

namespace App\Concrete;

use App\Blueprint\PayrollServiceInterface;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\SemiMonthlySequence;
use App\Models\Company;
use App\Models\PayFrequency as PayFrequencyModel;
use App\Traits\HasTime;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollServiceConcrete implements PayrollServiceInterface
{
    protected string $timezone = 'UTC';
    protected Carbon $now;
    protected Collection $payFrequencies;

    use HasTime;

    public function __construct(
        protected ?Company $company
    ){
        $this->timezone = $company?->timezone ?? 'UTC';
        $this->now = Carbon::now()->timezone($this->timezone);
        $this->payFrequencies = $company?->payFrequencies ?? collect();
    }

    public function setCustomDate(Carbon $date): void
    {
        $this->now = $date;
    }

    public function latestPayrolls(): array
    {
        if(empty($this->company)){
            return [];
        }

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
                'now' => $this->now->toDateString(),
                'weekly' => [
                    'year' => $latestWeeklyFrequency['year'],
                    'month' => $latestWeeklyFrequency['month'],
                    'month_sequence' => $latestWeeklyFrequency['month_sequence']?->label,
                    'start' => $latestWeeklyFrequency['start']?->toDateString(),
                    'end' => $latestWeeklyFrequency['end']?->toDateString(),
                ],
                'monthly' => [
                    'year' => $monthlyFrequencyLatestDateRange['year'],
                    'month' => $monthlyFrequencyLatestDateRange['month'],
                    'month_sequence' => $monthlyFrequencyLatestDateRange['month_sequence']?->label(),
                    'start' => $monthlyFrequencyLatestDateRange['start']?->toDateString(),
                    'end' => $monthlyFrequencyLatestDateRange['end']?->toDateString(),
                ],
                'semimonthly' => [
                    'year' => $semimonthlyFrequencyLatestDateRange['year'],
                    'month' => $semimonthlyFrequencyLatestDateRange['month'],
                    'month_sequence' => $semimonthlyFrequencyLatestDateRange['month_sequence']?->label(),
                    'start' => $semimonthlyFrequencyLatestDateRange['start']?->toDateString(),
                    'end' => $semimonthlyFrequencyLatestDateRange['end']?->toDateString(),
                ]
            ],
        ]);

        return [];
    }

    private function getFrequencyDateRange(?PayFrequencyModel $frequency): array
    {
        $payrollYear = null;
        $payrollMonth = null;
        $payrollMonthSequence = null;
        $startDate = null;
        $endDate = null;

        if($frequency->type == PayFrequencyEnum::WEEKLY){

            $startDate = $this->getPreviousWeekDay($this->now, $frequency->cut_off_value->value)->addDay();

            if($this->now->dayOfWeek == $frequency->cut_off_value->value){

                $endDate = $this->now;

            } else {
                $endDate = $startDate->copy()->addDays($frequency->days_span - 1);
            }

            $payrollYear = $endDate->year;
            $payrollMonth = $endDate->month;
        }

        if($frequency->type == PayFrequencyEnum::MONTHLY){

            if($frequency->timePeriodPreset->name == 'end_of_month_cut_off'){

                $startDate = $this->now->copy()->startOfMonth();
                $endDate = $this->now->copy()->endOfMonth();
                $payrollYear = $startDate->year;
                $payrollMonth = $startDate->month;
            } else {

                $startDay = (int)collect($frequency->period->cast)->where('key', 'start_date')->first()->value->day;
                $endDay = (int)collect($frequency->period->cast)->where('key', 'end_date')->first()->value->day;

                $startDate = $this->getPreviousNthIncludingCurrent($this->now, $startDay);
                $endDate = $this->getNextNthIncludingCurrent($this->now, $endDay);

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

                $firstHalfStartDay = $this->now->copy()->startOfMonth()->day;
                $firstHalfEndDay = (int)collect($frequency->period->cast)->where('key', '1st_half_end_date')->first()->value->day;
                $secondHalfStartDay = (int)collect($frequency->period->cast)->where('key', '2nd_half_start_date')->first()->value->day;
                $secondHalfEndDay = $this->now->copy()->endOfMonth()->day;

                if($this->now->day >= $secondHalfStartDay){
                    $startDate = $this->now->copy()->day($secondHalfStartDay);
                    $endDate = $this->now->copy()->day($secondHalfEndDay);
                    $payrollMonthSequence = SemiMonthlySequence::SECOND_HALF;
                } else {
                    $startDate = $this->now->copy()->day($firstHalfStartDay);
                    $endDate = $this->now->copy()->day($firstHalfEndDay);
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

                    if($this->now->day >= $firstHalfStartDay && $this->now->day <= $firstHalfEndDay){
                        $startDate = $this->getPreviousNthIncludingCurrent($this->now, $firstHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $firstHalfEndDay);
                        $payrollMonthSequence = SemiMonthlySequence::FIRST_HALF;
                    } else {
                        $startDate = $this->getPreviousNthIncludingCurrent($this->now, $secondHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $secondHalfEndDay);
                        $payrollMonthSequence = SemiMonthlySequence::SECOND_HALF;
                    }

                    $payrollYear = $startDate->year;
                    $payrollMonth = $startDate->month;
                }

                if(in_array($frequency->timePeriodPreset->name, ['20th_cut_off', '25th_cut_off'])){

                    if($this->now->day >= $secondHalfStartDay && $this->now->day <= $secondHalfEndDay){
                        $startDate = $this->getPreviousNthIncludingCurrent($this->now, $secondHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $secondHalfEndDay);
                        $payrollMonthSequence = SemiMonthlySequence::SECOND_HALF;
                    } else {
                        $startDate = $this->getPreviousNthIncludingCurrent($this->now, $firstHalfStartDay);
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
