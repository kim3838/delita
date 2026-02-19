<?php

namespace App\Traits;

use App\Concrete\MutableBigDecimal;
use App\Enums\Compensation as CompensationEnum;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\PayPeriod;
use App\Enums\SalaryStatementAttendanceDayType;
use App\Enums\SalaryStatementAttendanceStatus;
use App\Enums\WorkHourType;
use App\Models\EmployeePayrollComponent;
use App\Models\SalaryStatementAttendance;
use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use Brick\Math\BigNumber;
use Brick\Math\RoundingMode;

trait HasPayableDay
{
    public bool $holidayPayForfeiture = false;
    public array $workSplits = [];
    public array $overtimeSplits = [];

    public function statementAttendanceSetAmountableOnSplits(
        SalaryStatementAttendance $salaryStatementAttendance,
        $payloadMap,
        &$assignedEarningsPayload,
        &$globalEarningsPayload,
        $test = false,
        $debug = false,
    ): array{

        $debugDetailProxyModelUpdate = false;

        $basicPayPayloadKey = $payloadMap[CompensationEnum::BASIC_PAY->value] ?? null;
        $allowancePayloadKeys = $payloadMap[CompensationEnum::REGULAR_ALLOWANCE->value] ?? null;
        $overtimePayPayloadKey = $payloadMap[CompensationEnum::OVERTIME->value] ?? null;
        $leavePayPayloadKey = $payloadMap[CompensationEnum::LEAVE_PAY->value] ?? null;
        $holidayPayPayloadKey = $payloadMap[CompensationEnum::HOLIDAY_PAY->value] ?? null;

        $splitResults = [
            'work_splits' => [],
            'overtime_splits' => [],
            'payable_non_attendance_work_splits' => [],
        ];

        /**
         * Parameter overview, used to set test items
         **/
        if(!$test && false){
            $salaryStatementAttendanceArray = $salaryStatementAttendance->toArray();
            _debug([
                'salaryStatementAttendance' => [
                    'date' => $salaryStatementAttendanceArray['date'],
                    'status' => $salaryStatementAttendanceArray['status'],
                    'day_type' => $salaryStatementAttendanceArray['day_type']
                ],
                'holidayPayForfeiture' => $this->holidayPayForfeiture,
                'assignedEarningsPayload' => $assignedEarningsPayload,
                'globalEarningsPayload' => $globalEarningsPayload,
                'workSplits' => $this->workSplits,
                'overtimeSplits' => $this->overtimeSplits,
            ]);
        }

        list($isPresent, $isLeave, $isHoliday, $isDoubleHoliday, $isRegularWorkingDay, $isLegalHoliday, $isSpecialHoliday, $leaveWithoutPay, $leaveWithPay, $leaveWithoutPayAndIsLegalHoliday, $isAbsentAndLegalHoliday, $payableNoneAttendance)
            = $this->listSalaryStatementAttendanceStatusAndDayTypes($salaryStatementAttendance);
        $isRestDay = in_array($salaryStatementAttendance->date->dayOfWeek, $this->restDays);

        if($isPresent){

            $basicPayHourlyRate = $assignedEarningsPayload[$basicPayPayloadKey]['hourly_rate'] ?? BigDecimal::zero();

            if($isRegularWorkingDay){

                foreach($this->workSplits as $workSplit){
                    $proxyId = null;$proxyModel = null;
                    if(!$test){$proxyId = $workSplit['id'];$proxyModel = $workSplit['proxy_model'];}

                    $splitWorkHourType = $workSplit['work_hour_type'];
                    $splitRegularMultiplier = $workSplit['regular_rate_multiplier'];
                    $splitNonRestMultiplier = $workSplit['non_rest_rate_multiplier'];

                    $splitHourlyMultiplier = $workSplit['hourly_rate_multiplier'];
                    $splitBaseMultiplier = $workSplit['base_rate_multiplier'];
                    $splitActualPresent = $workSplit['actual_present'];

                    $regularPay = BigDecimal::zero();
                    $nightPay = BigDecimal::zero();
                    $restPay = BigDecimal::zero();
                    $hours = $splitActualPresent->dividedBy(BigInteger::of('60'), 6, RoundingMode::HalfUp);

                    if($splitWorkHourType == WorkHourType::REGULAR){

                        $regularMultiplier = $splitBaseMultiplier;
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier->minus($splitNonRestMultiplier) : BigDecimal::zero());

                        $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($regularMultiplier);
                        if($isRestDay){$restPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($restMultiplier);}

                    } else if($splitWorkHourType == WorkHourType::NIGHT){

                        $regularMultiplier = $splitBaseMultiplier;
                        $nightMultiplier = $splitHourlyMultiplier->minus($splitRegularMultiplier);
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier->minus($splitBaseMultiplier)->minus($nightMultiplier) : BigDecimal::zero());

                        $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($regularMultiplier);
                        $nightPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($nightMultiplier);
                        if($isRestDay){$restPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($restMultiplier);}
                    }

                    if(isset($assignedEarningsPayload[$basicPayPayloadKey])){

                        $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay'] =
                            $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay']->plus($regularPay);
                        $assignedEarningsPayload[$basicPayPayloadKey]['night_differential_pay'] =
                            $assignedEarningsPayload[$basicPayPayloadKey]['night_differential_pay']->plus($nightPay);
                        $assignedEarningsPayload[$basicPayPayloadKey]['rest_day_pay'] =
                            $assignedEarningsPayload[$basicPayPayloadKey]['rest_day_pay']->plus($restPay);

                        $assignedEarningsPayload[$basicPayPayloadKey]['total'] = $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay']
                            ->plus($assignedEarningsPayload[$basicPayPayloadKey]['night_differential_pay'])
                            ->plus($assignedEarningsPayload[$basicPayPayloadKey]['rest_day_pay']);
                    }
                    /**
                     * Allowance is always available as long as there is a working hour
                     **/
                    $splitTotalAllowance = new MutableBigDecimal();

                    foreach($allowancePayloadKeys as $allowancePayloadKey){

                        $allowanceHourlyRate = $assignedEarningsPayload[$allowancePayloadKey]['hourly_rate'] ?? BigDecimal::zero();
                        $allowanceValue = $splitActualPresent->dividedBy(BigInteger::of('60'), 6, RoundingMode::HalfUp)->multipliedBy($allowanceHourlyRate);

                        if(isset($assignedEarningsPayload[$allowancePayloadKey])){

                            $assignedEarningsPayload[$allowancePayloadKey]['regular_pay'] =
                                $assignedEarningsPayload[$allowancePayloadKey]['regular_pay']->plus($allowanceValue);
                            $splitTotalAllowance->plus($allowanceValue);

                            $assignedEarningsPayload[$allowancePayloadKey]['total'] = $assignedEarningsPayload[$allowancePayloadKey]['regular_pay'];
                        }
                    }

                    if($test || $debug){
                        $splitResults['work_splits'][] = [
                            'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                                $splitWorkHourType->label() . ' ' .
                                $salaryStatementAttendance->day_type->label() . ' ' .
                                ($isRestDay ? 'rest day' : 'non-rest day'),
                            'ACTUAL_PRESENT' => (string)$splitActualPresent,
                            'WORKED HOURS' => (string)$hours,
                            'REGULAR MULTIPLIER' => (string)$splitRegularMultiplier,
                            'NON-RESTMULTIPLIER' => (string)$splitNonRestMultiplier,
                            'HOURLYR_MULTIPLIER' => (string)$splitHourlyMultiplier,
                            'BASE_RA_MULTIPLIER' => (string)$splitBaseMultiplier,
                            'BASIC' => (string)($hours->multipliedBy($basicPayHourlyRate)),
                            ...($splitWorkHourType == WorkHourType::NIGHT ? ['NIGHT MULTIPLIER' => (string)$nightMultiplier] : []),
                            ...($isRestDay ? ['REST MULTIPLIER' => (string)$restMultiplier] : []),
                            '=>' => '=>',
                            'REGULAR_PAY' => (string)$regularPay,
                            'ALLOWANCE' => (string)$splitTotalAllowance,
                            'NIGHT_DIFFERENTIAL_PAY' => (string)$nightPay,
                            'REST_DAY_PAY' => (string)$restPay,
                        ];
                    }

                    if(!$test){
                        $updateProxyModelDetail = [
                            'hourly_rate' => (string)$basicPayHourlyRate,
                            'regular_pay' => (string)$regularPay,
                            'allowance' => (string)$splitTotalAllowance,
                            'night_differential_pay' => (string)$nightPay,
                            'rest_day_pay' => (string)$restPay,
                        ];

                        if($debugDetailProxyModelUpdate){
                            _debug([
                                'origin' => 'Regular work day work split',
                                'proxy_id' => $proxyId,
                                'proxy_model' => $proxyModel,
                                'update' => $updateProxyModelDetail
                            ]);
                        }

                        //Update detail proxy model
                        app($proxyModel)->model()::find($proxyId)->update($updateProxyModelDetail);
                    }
                }

                foreach($this->overtimeSplits as $overtimeSplit){
                    $proxyId = null;$proxyModel = null;
                    if(!$test){$proxyId = $overtimeSplit['id'];$proxyModel = $overtimeSplit['proxy_model'];}

                    $splitWorkHourType = $overtimeSplit['work_hour_type'];
                    $splitRegularMultiplier = $overtimeSplit['regular_rate_multiplier'];
                    $splitNonRestMultiplier = $overtimeSplit['non_rest_rate_multiplier'];

                    $splitHourlyMultiplier = $overtimeSplit['hourly_rate_multiplier'];
                    $splitBaseMultiplier = $overtimeSplit['base_rate_multiplier'];
                    $splitActualPresent = $overtimeSplit['actual_present'];

                    $regularPay = BigDecimal::zero();
                    $nightPay = BigDecimal::zero();
                    $restPay = BigDecimal::zero();
                    $hours = $splitActualPresent->dividedBy(BigInteger::of('60'), 6, RoundingMode::HalfUp);

                    if($splitWorkHourType == WorkHourType::REGULAR){

                        $regularMultiplier = $splitBaseMultiplier;
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier->minus($splitNonRestMultiplier) : BigDecimal::zero());

                        $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($regularMultiplier);
                        if($isRestDay){$restPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($restMultiplier);}

                    } else if($splitWorkHourType == WorkHourType::NIGHT){

                        $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($splitBaseMultiplier);

                        $nightMultiplier = $splitHourlyMultiplier->minus($splitRegularMultiplier);
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier->minus($splitBaseMultiplier)->minus($nightMultiplier) : BigDecimal::zero());

                        $nightPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($nightMultiplier);
                        if($isRestDay){$restPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($restMultiplier);}
                    }

                    if(isset($assignedEarningsPayload[$overtimePayPayloadKey])){
                        $assignedEarningsPayload[$overtimePayPayloadKey]['regular_pay'] =
                            $assignedEarningsPayload[$overtimePayPayloadKey]['regular_pay']->plus($regularPay);
                        $assignedEarningsPayload[$overtimePayPayloadKey]['night_differential_pay'] =
                            $assignedEarningsPayload[$overtimePayPayloadKey]['night_differential_pay']->plus($nightPay);
                        $assignedEarningsPayload[$overtimePayPayloadKey]['rest_day_pay'] =
                            $assignedEarningsPayload[$overtimePayPayloadKey]['rest_day_pay']->plus($restPay);

                        $assignedEarningsPayload[$overtimePayPayloadKey]['total'] = $assignedEarningsPayload[$overtimePayPayloadKey]['regular_pay']
                            ->plus($assignedEarningsPayload[$overtimePayPayloadKey]['night_differential_pay'])
                            ->plus($assignedEarningsPayload[$overtimePayPayloadKey]['rest_day_pay']);
                    }

                    if($test || $debug){
                        $splitResults['overtime_splits'][] = [
                            'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                                $splitWorkHourType->label() . ' ' .
                                $salaryStatementAttendance->day_type->label() . ' ' .
                                ($isRestDay ? 'rest day' : 'non-rest day'),
                            'ACTUAL_PRESENT' => (string)$splitActualPresent,
                            'WORKED HOURS' => (string)$hours,
                            'REGULAR MULTIPLIER' => (string)$splitRegularMultiplier,
                            'NON-RESTMULTIPLIER' => (string)$splitNonRestMultiplier,
                            'HOURLYR_MULTIPLIER' => (string)$splitHourlyMultiplier,
                            'BASE_RA_MULTIPLIER' => (string)$splitBaseMultiplier,
                            'BASIC' => (string)($hours->multipliedBy($basicPayHourlyRate)),
                            ...($splitWorkHourType == WorkHourType::NIGHT ? ['NIGHT MULTIPLIER' => (string)$nightMultiplier] : []),
                            ...($isRestDay ? ['REST MULTIPLIER' => (string)$restMultiplier] : []),
                            '=>' => '=>',
                            'REGULAR_PAY' => (string)$regularPay,
                            'NIGHT_DIFFERENTIAL_PAY' => (string)$nightPay,
                            'REST_DAY_PAY' => (string)$restPay,
                        ];
                    }

                    if(!$test){
                        $updateProxyModelDetail = [
                            'hourly_rate' => (string)$basicPayHourlyRate,
                            'regular_pay' => (string)$regularPay,
                            'night_differential_pay' => (string)$nightPay,
                            'rest_day_pay' => (string)$restPay,
                        ];

                        if($debugDetailProxyModelUpdate){
                            _debug([
                                'origin' => 'Regular work day overtime split',
                                'proxy_id' => $proxyId,
                                'proxy_model' => $proxyModel,
                                'update' => $updateProxyModelDetail
                            ]);
                        }

                        //Update detail proxy model
                        app($proxyModel)->model()::find($proxyId)->update($updateProxyModelDetail);
                    }
                }
            }

            if($isHoliday){

                foreach($this->workSplits as $workSplit){
                    $proxyId = null;$proxyModel = null;
                    if(!$test){$proxyId = $workSplit['id'];$proxyModel = $workSplit['proxy_model'];}

                    $splitWorkHourType = $workSplit['work_hour_type'];
                    $splitRegularMultiplier = $workSplit['regular_rate_multiplier'];
                    $splitNonRestMultiplier = $workSplit['non_rest_rate_multiplier'];

                    $splitHourlyMultiplier = $workSplit['hourly_rate_multiplier'];
                    //If double holiday, replace the base rate by 2
                    $splitBaseMultiplier = $isDoubleHoliday ? BigDecimal::of('2') : $workSplit['base_rate_multiplier'];
                    $splitActualPresent = $workSplit['actual_present'];

                    $regularPay = BigDecimal::zero();
                    $nightPay = BigDecimal::zero();
                    $holidayPay = BigDecimal::zero();
                    $restPay = BigDecimal::zero();
                    $hours = $splitActualPresent->dividedBy(BigInteger::of('60'), 6, RoundingMode::HalfUp);

                    if($splitWorkHourType == WorkHourType::REGULAR){

                        $regularMultiplier = $splitBaseMultiplier;
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier->minus($splitNonRestMultiplier) : BigDecimal::zero());
                        $holidayMultiplier = $splitHourlyMultiplier->minus($splitBaseMultiplier)->minus($restMultiplier);

                        $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($regularMultiplier);
                        $holidayPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($holidayMultiplier);
                        if($isRestDay){$restPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($restMultiplier);}

                    } else if($splitWorkHourType == WorkHourType::NIGHT){

                        $regularMultiplier = $splitBaseMultiplier;
                        $nightMultiplier = $splitHourlyMultiplier->minus($splitRegularMultiplier);
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier->minus($splitNonRestMultiplier) : BigDecimal::zero());
                        $holidayMultiplier = $splitHourlyMultiplier->minus($splitBaseMultiplier)->minus($nightMultiplier)->minus($restMultiplier);

                        $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($regularMultiplier);
                        $holidayPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($holidayMultiplier);
                        $nightPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($nightMultiplier);
                        if($isRestDay){$restPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($restMultiplier);}
                    }

                    //Basic pay
                    if(isset($assignedEarningsPayload[$basicPayPayloadKey])){

                        $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay'] =
                            $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay']->plus($regularPay);
                        $assignedEarningsPayload[$basicPayPayloadKey]['night_differential_pay'] =
                            $assignedEarningsPayload[$basicPayPayloadKey]['night_differential_pay']->plus($nightPay);
                        $assignedEarningsPayload[$basicPayPayloadKey]['rest_day_pay'] =
                            $assignedEarningsPayload[$basicPayPayloadKey]['rest_day_pay']->plus($restPay);

                        $assignedEarningsPayload[$basicPayPayloadKey]['total'] = $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay']
                            ->plus($assignedEarningsPayload[$basicPayPayloadKey]['night_differential_pay'])
                            ->plus($assignedEarningsPayload[$basicPayPayloadKey]['rest_day_pay']);
                    }

                    //Holiday pay
                    if(isset($globalEarningsPayload[$holidayPayPayloadKey])){

                        $globalEarningsPayload[$holidayPayPayloadKey]['regular_pay'] =
                            $globalEarningsPayload[$holidayPayPayloadKey]['regular_pay']->plus($holidayPay);

                        $globalEarningsPayload[$holidayPayPayloadKey]['total'] = $globalEarningsPayload[$holidayPayPayloadKey]['regular_pay']
                            ->plus($globalEarningsPayload[$holidayPayPayloadKey]['night_differential_pay']);
                    }

                    /**
                     * Allowance is always available as long as there is a working hour
                     **/
                    $splitTotalAllowance = new MutableBigDecimal();

                    foreach($allowancePayloadKeys as $allowancePayloadKey){

                        $allowanceHourlyRate = $assignedEarningsPayload[$allowancePayloadKey]['hourly_rate'] ?? BigDecimal::zero();
                        $allowanceValue = $splitActualPresent->dividedBy(BigInteger::of('60'), 6, RoundingMode::HalfUp)->multipliedBy($allowanceHourlyRate);

                        if(isset($assignedEarningsPayload[$allowancePayloadKey])){

                            $assignedEarningsPayload[$allowancePayloadKey]['regular_pay'] =
                                $assignedEarningsPayload[$allowancePayloadKey]['regular_pay']->plus($allowanceValue);
                            $splitTotalAllowance->plus($allowanceValue);

                            $assignedEarningsPayload[$allowancePayloadKey]['total'] = $assignedEarningsPayload[$allowancePayloadKey]['regular_pay'];
                        }
                    }

                    if($test || $debug){
                        $splitResults['work_splits'][] = [
                            'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                                $splitWorkHourType->label() . ' ' .
                                $salaryStatementAttendance->day_type->label() . ' ' .
                                ($isRestDay ? 'rest day' : 'non-rest day'),
                            'ACTUAL_PRESENT' => (string)$splitActualPresent,
                            'WORKED HOURS' => (string)$hours,
                            'REGULAR MULTIPLIER' => (string)$splitRegularMultiplier,
                            'NON-RESTMULTIPLIER' => (string)$splitNonRestMultiplier,
                            'HOURLYR_MULTIPLIER' => (string)$splitHourlyMultiplier,
                            'BASE_RA_MULTIPLIER' => (string)$splitBaseMultiplier,
                            'BASIC' => (string)($hours->multipliedBy($basicPayHourlyRate)),
                            ...($splitWorkHourType == WorkHourType::NIGHT ? ['NIGHT MULTIPLIER' => (string)$nightMultiplier] : []),
                            ...($isRestDay ? ['REST MULTIPLIER' => (string)$restMultiplier] : []),
                            'HOLIDAY MULTIPLIER' => (string)$holidayMultiplier,
                            '=>' => '=>',
                            'REGULAR_PAY' => (string)$regularPay,
                            'ALLOWANCE' => (string)$splitTotalAllowance,
                            'NIGHT_DIFFERENTIAL_PAY' => (string)$nightPay,
                            'REST_DAY_PAY' => (string)$restPay,
                            'HOLIDAY_PAY' => (string)$holidayPay
                        ];
                    }

                    if(!$test){
                        $updateProxyModelDetail = [
                            'hourly_rate' => (string)$basicPayHourlyRate,
                            'regular_pay' => (string)$regularPay,
                            'allowance' => (string)$splitTotalAllowance,
                            'night_differential_pay' => (string)$nightPay,
                            'rest_day_pay' => (string)$restPay,
                            'holiday_pay' => (string)$holidayPay,
                        ];

                        if($debugDetailProxyModelUpdate){
                            _debug([
                                'origin' => 'Holiday work split',
                                'proxy_id' => $proxyId,
                                'proxy_model' => $proxyModel,
                                'update' => $updateProxyModelDetail
                            ]);
                        }

                        //Update detail proxy model
                        app($proxyModel)->model()::find($proxyId)->update($updateProxyModelDetail);
                    }
                }

                foreach($this->overtimeSplits as $overtimeSplit){
                    $proxyId = null;$proxyModel = null;
                    if(!$test){$proxyId = $overtimeSplit['id'];$proxyModel = $overtimeSplit['proxy_model'];}

                    $splitWorkHourType = $overtimeSplit['work_hour_type'];
                    $splitRegularMultiplier = $overtimeSplit['regular_rate_multiplier'];
                    $splitNonRestMultiplier = $overtimeSplit['non_rest_rate_multiplier'];

                    $splitHourlyMultiplier = $overtimeSplit['hourly_rate_multiplier'];
                    $splitBaseMultiplier = $overtimeSplit['base_rate_multiplier'];
                    $splitActualPresent = $overtimeSplit['actual_present'];

                    $regularPay = BigDecimal::zero();
                    $nightPay = BigDecimal::zero();
                    $holidayPay = BigDecimal::zero();
                    $restPay = BigDecimal::zero();
                    $hours = $splitActualPresent->dividedBy(BigInteger::of('60'), 6, RoundingMode::HalfUp);

                    if($splitWorkHourType == WorkHourType::REGULAR){

                        $regularMultiplier = $splitBaseMultiplier;
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier->minus($splitNonRestMultiplier) : BigDecimal::zero());
                        $holidayMultiplier = $splitHourlyMultiplier->minus($splitBaseMultiplier)->minus($restMultiplier);

                        $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($regularMultiplier);
                        $holidayPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($holidayMultiplier);
                        if($isRestDay){$restPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($restMultiplier);}

                    } else if($splitWorkHourType == WorkHourType::NIGHT){

                        $regularMultiplier = $splitBaseMultiplier;
                        $nightMultiplier = $splitHourlyMultiplier->minus($splitRegularMultiplier);
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier->minus($splitNonRestMultiplier) : BigDecimal::zero());
                        $holidayMultiplier = $splitHourlyMultiplier->minus($splitBaseMultiplier)->minus($nightMultiplier)->minus($restMultiplier);

                        $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($regularMultiplier);
                        $holidayPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($holidayMultiplier);
                        $nightPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($nightMultiplier);
                        if($isRestDay){$restPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($restMultiplier);}
                    }

                    //Overtime pay
                    if(isset($assignedEarningsPayload[$overtimePayPayloadKey])){

                        $assignedEarningsPayload[$overtimePayPayloadKey]['regular_pay'] =
                            $assignedEarningsPayload[$overtimePayPayloadKey]['regular_pay']->plus($regularPay);
                        $assignedEarningsPayload[$overtimePayPayloadKey]['night_differential_pay'] =
                            $assignedEarningsPayload[$overtimePayPayloadKey]['night_differential_pay']->plus($nightPay);
                        $assignedEarningsPayload[$overtimePayPayloadKey]['rest_day_pay'] =
                            $assignedEarningsPayload[$overtimePayPayloadKey]['rest_day_pay']->plus($restPay);

                        $assignedEarningsPayload[$overtimePayPayloadKey]['total'] = $assignedEarningsPayload[$overtimePayPayloadKey]['regular_pay']
                            ->plus($assignedEarningsPayload[$overtimePayPayloadKey]['night_differential_pay'])
                            ->plus($assignedEarningsPayload[$overtimePayPayloadKey]['rest_day_pay']);
                    }

                    //Holiday pay
                    if(isset($globalEarningsPayload[$holidayPayPayloadKey])){

                        $globalEarningsPayload[$holidayPayPayloadKey]['regular_pay'] =
                            $globalEarningsPayload[$holidayPayPayloadKey]['regular_pay']->plus($holidayPay);

                        $globalEarningsPayload[$holidayPayPayloadKey]['total'] = $globalEarningsPayload[$holidayPayPayloadKey]['regular_pay']
                            ->plus($globalEarningsPayload[$holidayPayPayloadKey]['night_differential_pay']);
                    }

                    if($test || $debug){
                        $splitResults['overtime_splits'][] = [
                            'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                                $splitWorkHourType->label() . ' ' .
                                $salaryStatementAttendance->day_type->label() . ' ' .
                                ($isRestDay ? 'rest day' : 'non-rest day'),
                            'ACTUAL_PRESENT' => (string)$splitActualPresent,
                            'WORKED HOURS' => (string)$hours,
                            'REGULAR MULTIPLIER' => (string)$splitRegularMultiplier,
                            'NON-RESTMULTIPLIER' => (string)$splitNonRestMultiplier,
                            'HOURLYR_MULTIPLIER' => (string)$splitHourlyMultiplier,
                            'BASE_RA_MULTIPLIER' => (string)$splitBaseMultiplier,
                            'BASIC' => (string)($hours->multipliedBy($basicPayHourlyRate)),
                            ...($splitWorkHourType == WorkHourType::NIGHT ? ['NIGHT MULTIPLIER' => (string)$nightMultiplier] : []),
                            ...($isRestDay ? ['REST MULTIPLIER' => (string)$restMultiplier] : []),
                            'HOLIDAY MULTIPLIER' => (string)$holidayMultiplier,
                            '=>' => '=>',
                            'REGULAR_PAY' => (string)$regularPay,
                            'NIGHT_DIFFERENTIAL_PAY' => (string)$nightPay,
                            'REST_DAY_PAY' => (string)$restPay,
                            'HOLIDAY_PAY' => (string)$holidayPay
                        ];
                    }

                    if(!$test){
                        $updateProxyModelDetail = [
                            'hourly_rate' => (string)$basicPayHourlyRate,
                            'regular_pay' => (string)$regularPay,
                            'night_differential_pay' => (string)$nightPay,
                            'rest_day_pay' => (string)$restPay,
                            'holiday_pay' => (string)$holidayPay,
                        ];

                        if($debugDetailProxyModelUpdate){
                            _debug([
                                'origin' => 'Holiday overtime split',
                                'proxy_id' => $proxyId,
                                'proxy_model' => $proxyModel,
                                'update' => $updateProxyModelDetail
                            ]);
                        }

                        //Update detail proxy model
                        app($proxyModel)->model()::find($proxyId)->update($updateProxyModelDetail);
                    }
                }
            }
        }

        if(!$isPresent && $payableNoneAttendance){

            $basicPayHourlyRate = $assignedEarningsPayload[$basicPayPayloadKey]['hourly_rate'] ?? BigDecimal::zero();

            /**
             * If the date is holiday, leave without pay, and holiday setting has holiday pay forfeiture enabled,
             * Chain into preceding work days to identify if holiday pay has to be forfeited
             **/
            $holidayPayForfeitureEnabled = $isLegalHoliday && !$leaveWithPay && $this->holidayPayForfeiture;
            $forfeitHolidayPay =  $holidayPayForfeitureEnabled &&
                $this->isHolidayPayShouldBeForfeited($salaryStatementAttendance);

            foreach($this->workSplits as $workSplit){
                $proxyId = null;$proxyModel = null;
                if(!$test){$proxyId = $workSplit['id'];$proxyModel = $workSplit['proxy_model'];}

                $splitWorkHourType = $workSplit['work_hour_type'];
                $splitHourlyMultiplier = $workSplit['hourly_rate_multiplier'];
                //If double holiday, replace the base rate by 2
                $splitBaseMultiplier = $isDoubleHoliday ? BigDecimal::of('2') : $workSplit['base_rate_multiplier'];
                //If holiday pay forfeited, replace the base rate by 0
                $splitBaseMultiplier = $forfeitHolidayPay ? BigDecimal::zero() : $splitBaseMultiplier;
                $splitSplitDuration = $workSplit['split_duration'];
                $splitActualPresent = $workSplit['actual_present'];

                $hours = $splitSplitDuration->dividedBy(BigInteger::of('60'), 6, RoundingMode::HalfUp);

                /**
                 * Pay is regular pay when legal holiday, otherwise leave pay
                 **/
                $regularPay = $hours->multipliedBy($basicPayHourlyRate)->multipliedBy($splitBaseMultiplier);

                if($isLegalHoliday){
                    if(isset($assignedEarningsPayload[$basicPayPayloadKey])){
                        $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay'] =
                            $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay']->plus($regularPay);

                        $assignedEarningsPayload[$basicPayPayloadKey]['total'] =
                            $assignedEarningsPayload[$basicPayPayloadKey]['regular_pay'];
                    }
                }

                if($leaveWithPay && !$isLegalHoliday){
                    if(isset($globalEarningsPayload[$leavePayPayloadKey])){
                        $globalEarningsPayload[$leavePayPayloadKey]['regular_pay'] =
                            $globalEarningsPayload[$leavePayPayloadKey]['regular_pay']->plus($regularPay);

                        $globalEarningsPayload[$leavePayPayloadKey]['total'] =
                            $globalEarningsPayload[$leavePayPayloadKey]['regular_pay'];
                    }
                }

                $payableNonAttendance = [];
                $updateProxyModelDetail = [
                    'hourly_rate' => (string)$basicPayHourlyRate,
                    ...($holidayPayForfeitureEnabled ? [
                        'holiday_pay_forfeited' => $forfeitHolidayPay
                    ] : [])
                ];

                if($isLegalHoliday){
                    $payableNonAttendance['REGULAR_PAY'] = (string)$regularPay;
                    $updateProxyModelDetail['regular_pay'] = (string)$regularPay;
                }

                if($leaveWithPay && !$isLegalHoliday){
                    $payableNonAttendance['LEAVE_PAY'] = (string)$regularPay;
                    $updateProxyModelDetail['leave_pay'] = (string)$regularPay;
                }

                if($test || $debug){

                    $splitResults['payable_non_attendance_work_splits'][] = [
                        'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                            $salaryStatementAttendance->day_type->label() . ' ' .
                            ($isRestDay ? 'rest day' : 'non-rest day'),
                        'work_hour_type' => $splitWorkHourType->label(),
                        'ACTUAL_PRESENT' => (string)$splitActualPresent,
                        'SPLIT_DURATION' => (string)$splitSplitDuration,
                        'HOURLYR_MULTIPLIER' => (string)$splitHourlyMultiplier,
                        'BASE_RA_MULTIPLIER' => (string)$splitBaseMultiplier,
                        'BASIC' => (string)($hours->multipliedBy($basicPayHourlyRate)),
                        '=>' => '=>',
                        ...$payableNonAttendance
                    ];
                }

                if(!$test){
                    if($debugDetailProxyModelUpdate){
                        _debug([
                            'origin' => 'Holiday work payable non-attendance split',
                            'proxy_id' => $proxyId,
                            'proxy_model' => $proxyModel,
                            'update' => $updateProxyModelDetail
                        ]);
                    }

                    //Update detail proxy model
                    app($proxyModel)->model()::find($proxyId)->update($updateProxyModelDetail);
                }
            }
        }

        if($debug){
            _debug([
                'HasPayableDay split results' => $splitResults
            ]);
        }

        return $splitResults;
    }

    public function isHolidayPayShouldBeForfeited(SalaryStatementAttendance $salaryStatementAttendance): bool
    {
        $debugEnabled = false;

        $precedingAttendanceEloquentQueryBuilder = SalaryStatementAttendance::query()
            ->whereHas('salaryStatement', function ($query) use ($salaryStatementAttendance) {
                $query->where('employee_id', $salaryStatementAttendance->salaryStatement->employee_id);
            })
            ->where('date', '<', $salaryStatementAttendance->date->toDateString())
            ->whereNotIn('status', [
                SalaryStatementAttendanceStatus::DAY_OFF,
            ])
            ->orderByDesc('date');

        $precedingAttendance = $precedingAttendanceEloquentQueryBuilder->first();

        if (!$precedingAttendance) {
            return false;
        }

        if($debugEnabled){
            _debug([
                'Preceding attendance' => [
                    'date' => $precedingAttendance->date->toDateString(),
                    'status' => $precedingAttendance->status?->label(),
                    'day_type' => $precedingAttendance->day_type?->label(),
                ],
            ]);
        }

        return in_array($precedingAttendance->status, [
            SalaryStatementAttendanceStatus::ABSENT,
            SalaryStatementAttendanceStatus::LEAVE_WITHOUT_PAY,
        ]);
    }

    public function listSalaryStatementAttendanceStatusAndDayTypes(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        $isPresent = in_array($salaryStatementAttendance->status, [SalaryStatementAttendanceStatus::FULL_PRESENT, SalaryStatementAttendanceStatus::PRESENT_WITH_IRREGULARITIES]);
        $isLeave = in_array($salaryStatementAttendance->status, [SalaryStatementAttendanceStatus::LEAVE_WITHOUT_PAY, SalaryStatementAttendanceStatus::LEAVE_WITH_PAY]);
        $isHoliday = in_array($salaryStatementAttendance->day_type, [SalaryStatementAttendanceDayType::SPECIAL_HOLIDAY, SalaryStatementAttendanceDayType::LEGAL_HOLIDAY, SalaryStatementAttendanceDayType::DOUBLE_HOLIDAY]);
        $isDoubleHoliday = $salaryStatementAttendance->day_type == SalaryStatementAttendanceDayType::DOUBLE_HOLIDAY;

        $isRegularWorkingDay = $salaryStatementAttendance->day_type == SalaryStatementAttendanceDayType::WORKING_DAY;
        $isLegalHoliday = in_array($salaryStatementAttendance->day_type, [SalaryStatementAttendanceDayType::LEGAL_HOLIDAY, SalaryStatementAttendanceDayType::DOUBLE_HOLIDAY,]);
        $isSpecialHoliday = $salaryStatementAttendance->day_type == SalaryStatementAttendanceDayType::SPECIAL_HOLIDAY;

        $leaveWithoutPay = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::LEAVE_WITHOUT_PAY;
        $leaveWithPay = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::LEAVE_WITH_PAY;
        $leaveWithoutPayAndIsLegalHoliday = $leaveWithoutPay && $isLegalHoliday;

        $isAbsentAndLegalHoliday = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::ABSENT && $isLegalHoliday;
        $isDayOffAndLegalHoliday = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::DAY_OFF && $isLegalHoliday;

        $payableNoneAttendance = $leaveWithPay || $leaveWithoutPayAndIsLegalHoliday || $isAbsentAndLegalHoliday || $isDayOffAndLegalHoliday;

        return [
            $isPresent,
            $isLeave,
            $isHoliday,
            $isDoubleHoliday,

            $isRegularWorkingDay,
            $isLegalHoliday,
            $isSpecialHoliday,

            $leaveWithoutPay,
            $leaveWithPay,
            $leaveWithoutPayAndIsLegalHoliday,

            $isAbsentAndLegalHoliday,

            $payableNoneAttendance,
        ];
    }

    public function getAssignedPayrollComponentHourlyRate(
        PayFrequencyEnum $payrollFrequency,
        EmployeePayrollComponent $amountablePayrollComponent,
        $totalWorkMinutes
    ): BigNumber {

        $hourlyRate = BigDecimal::zero();
        $payrollComponentAmount = BigDecimal::of($amountablePayrollComponent->amount);

        if($payrollFrequency === PayFrequencyEnum::MONTHLY){

            $hourlyRate = match($amountablePayrollComponent->pay_period){
                PayPeriod::MONTHLY => ($payrollComponentAmount->dividedBy(BigInteger::of((string)$this->frequencyWorkingDayCount), 6, RoundingMode::HalfUp))
                    ->dividedBy(BigInteger::of($totalWorkMinutes)->dividedBy(BigInteger::of('60')), 6, RoundingMode::HalfUp),

                PayPeriod::SEMI_MONTHLY => (($payrollComponentAmount->multipliedBy(BigInteger::of('2')))->dividedBy(BigInteger::of((string)$this->frequencyWorkingDayCount), 6, RoundingMode::HalfUp))
                    ->dividedBy(BigInteger::of($totalWorkMinutes)->dividedBy(BigInteger::of('60')), 6, RoundingMode::HalfUp),

                PayPeriod::DAILY => $payrollComponentAmount->dividedBy(BigInteger::of($totalWorkMinutes)->dividedBy(BigInteger::of('60')), 6, RoundingMode::HalfUp),

                //Return if hourly
                default => $payrollComponentAmount
            };
        }

        if($payrollFrequency === PayFrequencyEnum::SEMI_MONTHLY){

            $hourlyRate = match($amountablePayrollComponent->pay_period){
                PayPeriod::MONTHLY => (($payrollComponentAmount->dividedBy(BigInteger::of('2'), 6, RoundingMode::HalfUp))->dividedBy(BigInteger::of((string)$this->frequencyWorkingDayCount), 6, RoundingMode::HalfUp))
                    ->dividedBy(BigInteger::of($totalWorkMinutes)->dividedBy(BigInteger::of('60')), 6, RoundingMode::HalfUp),

                PayPeriod::SEMI_MONTHLY => ($payrollComponentAmount->dividedBy(BigInteger::of((string)$this->frequencyWorkingDayCount), 6, RoundingMode::HalfUp))
                    ->dividedBy(BigInteger::of($totalWorkMinutes)->dividedBy(BigInteger::of('60')), 6, RoundingMode::HalfUp),

                PayPeriod::DAILY => $payrollComponentAmount->dividedBy(BigInteger::of($totalWorkMinutes)->dividedBy(BigInteger::of('60')), 6, RoundingMode::HalfUp),

                //Return if hourly
                default => $payrollComponentAmount
            };
        }

        return $hourlyRate;
    }
}
