<?php

namespace App\Concrete;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\PayrollPayloadRepository;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\SemiMonthlySequence;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\Hydrations\Payroll\PayrollPayload;
use App\Models\PayFrequency as PayFrequencyModel;
use App\Traits\HasPayroll;
use App\Traits\HasTime;
use App\Transformers\PayrollPayload\BasicTransformer;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class PayrollServiceConcrete implements PayrollServiceInterface
{
    protected string $timezone = 'UTC';
    public Carbon $date;
    protected Collection $payFrequencies;

    use HasTime, HasPayroll;

    public function __construct(
        protected ?Company $company
    ){
        $this->timezone = $company?->timezone ?? 'UTC';
        $this->date = Carbon::now()->timezone($this->timezone);
        $this->payFrequencies = $company?->payFrequencies ?? collect();
    }

    public function resetDate(): void
    {
        $this->date = Carbon::now()->timezone($this->timezone);
    }

    public function setCustomDate(Carbon $date): void
    {
        $this->date = $date;
    }

    public function getLatestWithRecent($payFrequencyEnumValue = [], $recentCount = 1): array
    {
        $recentPayrolls = [];
        $latestPayrolls = [];

        sort($payFrequencyEnumValue);

        foreach($payFrequencyEnumValue as $payFrequency){

            $payFrequencyRecentPayrolls = [];

            $payFrequencyLatestPayroll = $this->getPayrollPayload($payFrequency);
            $chainStartDate = $payFrequencyLatestPayroll->start->copy();

            while(count($payFrequencyRecentPayrolls) < $recentCount){

                $this->setCustomDate($chainStartDate->subDay());

                $recent = $this->getPayrollPayload($payFrequency);

                $chainStartDate = $recent->start->copy();

                array_unshift($payFrequencyRecentPayrolls, $recent);
            }

            $latestPayrolls[] = $payFrequencyLatestPayroll;

            foreach($payFrequencyRecentPayrolls as $payFrequencyRecentPayroll){
                $recentPayrolls[] = $payFrequencyRecentPayroll;
            }

            $this->resetDate();
        }

        return [
            'recent' => collect($recentPayrolls),
            'latest' => collect($latestPayrolls),
        ];
    }

    public function getPayrollPayload($payFrequencyEnumValue = null): ?PayrollPayload
    {
        $debugEnabled = false;

        if(empty($this->company)){
            return null;
        }

        if($debugEnabled){
            $weekly = PayFrequencyEnum::WEEKLY;
            $semimonthly = PayFrequencyEnum::SEMI_MONTHLY;
            $monthly = PayFrequencyEnum::MONTHLY;

            $weeklyFrequency = $this->payFrequencies->where('type', $weekly->value)->first();
            $semimonthlyFrequency = $this->payFrequencies->where('type', $semimonthly->value)->first();
            $monthlyFrequency = $this->payFrequencies->where('type', $monthly->value)->first();

            $latestWeekly = $this->getPayrollPayloadByFrequency($weeklyFrequency);
            $latestMonthlyFrequency = $this->getPayrollPayloadByFrequency($monthlyFrequency);
            $latestSemimonthlyFrequency = $this->getPayrollPayloadByFrequency($semimonthlyFrequency);

            _debug([
                'date' => $this->date->toDateString(),
                'weekly' => Fractal::item($latestWeekly, BasicTransformer::class),
                'monthly' => Fractal::item($latestMonthlyFrequency, BasicTransformer::class),
                'semimonthly' => Fractal::item($latestSemimonthlyFrequency, BasicTransformer::class),
            ]);
        }

        $payrollPayload = $this->getPayrollPayloadByFrequency($this->payFrequencies->where('type', $payFrequencyEnumValue)->first());

        return $payrollPayload;
    }

    /**
     * @throws BindingResolutionException
     */
    private function getPayrollPayloadByFrequency(?PayFrequencyModel $frequency): PayrollPayload
    {
        $payrollYear = null;
        $payrollMonth = null;
        $frequencySequence = null;
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
                    $frequencySequence = SemiMonthlySequence::SECOND_HALF;
                } else {
                    $startDate = $this->date->copy()->day($firstHalfStartDay);
                    $endDate = $this->date->copy()->day($firstHalfEndDay);
                    $frequencySequence = SemiMonthlySequence::FIRST_HALF;
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
                        $frequencySequence = SemiMonthlySequence::FIRST_HALF;
                    } else {
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $secondHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $secondHalfEndDay);
                        $frequencySequence = SemiMonthlySequence::SECOND_HALF;
                    }

                    $payrollYear = $startDate->year;
                    $payrollMonth = $startDate->month;
                }

                if(in_array($frequency->timePeriodPreset->name, ['20th_cut_off', '25th_cut_off'])){

                    if($this->date->day >= $secondHalfStartDay && $this->date->day <= $secondHalfEndDay){
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $secondHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $secondHalfEndDay);
                        $frequencySequence = SemiMonthlySequence::SECOND_HALF;
                    } else {
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $firstHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $firstHalfEndDay);
                        $frequencySequence = SemiMonthlySequence::FIRST_HALF;
                    }

                    $payrollYear = $endDate->year;
                    $payrollMonth = $endDate->month;
                }
            }
        }

        return App::make(PayrollPayloadRepository::class)->hydrateItem([
            'year' => $payrollYear,
            'month' => $payrollMonth,
            'pay_frequency' => $frequency->type->value,
            'frequency_sequence' => $frequencySequence?->value,
            'start' => $startDate,
            'end' => $endDate,
        ]);
    }
}
