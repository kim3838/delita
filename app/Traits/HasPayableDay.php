<?php

namespace App\Traits;

use App\Enums\Compensation as CompensationEnum;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\PayPeriod;
use App\Enums\SalaryStatementAttendanceDayType;
use App\Enums\SalaryStatementAttendanceStatus;
use App\Enums\WorkHourType;
use App\Models\EmployeePayrollComponent;
use App\Models\SalaryStatementAttendance;

trait HasPayableDay
{
    public array $workSplits = [];
    public array $overtimeSplits = [];

    public function setSatementDateAmountOnEarningsPayload(
        SalaryStatementAttendance $salaryStatementAttendance,
        &$assignedEarningsPayload,
        &$globalEarningsPayload,
        $test = false,
        $debug = false,
    ){
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
                'assignedEarningsPayload' => $assignedEarningsPayload,
                'globalEarningsPayload' => $globalEarningsPayload,
                'workSplits' => $this->workSplits,
                'overtimeSplits' => $this->overtimeSplits,
            ]);
        }

        list($isPresent, $isLeave, $isHoliday, $isRegularWorkingDay, $isLegalHoliday, $isSpecialHoliday, $leaveWithoutPay, $leaveWithPay, $leaveWithoutPayAndIsLegalHoliday, $isAbsentAndLegalHoliday, $payableNoneAttendance) = $this->listSalaryStatementAttendanceStatusAndDayTypes($salaryStatementAttendance);
        $isRestDay = in_array($salaryStatementAttendance->date->dayOfWeek, $this->restDays);

        if($isPresent){

            $basicPayHourlyRate = $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['hourly_rate'] ?? 0;
            $allowanceHourlyRate = $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['hourly_rate'] ?? 0;

            if($isRegularWorkingDay){

                foreach($this->workSplits as $workSplit){
                    if(!$test){$detailId = $workSplit['id'];$proxyModel = $workSplit['proxy_model'];}

                    $splitWorkHourType = $workSplit['work_hour_type'];
                    $splitRegularMultiplier = $workSplit['regular_rate_multiplier'];
                    $splitNonRestMultiplier = $workSplit['non_rest_rate_multiplier'];

                    $splitHourlyMultiplier = $workSplit['hourly_rate_multiplier'];
                    $splitBaseMultiplier = $workSplit['base_rate_multiplier'];
                    $splitActualPresent = $workSplit['actual_present'];

                    $regularPay = 0;$nightPay = 0;$restPay = 0;$hours = ($splitActualPresent / 60);

                    if($splitWorkHourType == WorkHourType::REGULAR){

                        $regularMultiplier = $splitBaseMultiplier;
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier - $splitNonRestMultiplier : 0);

                        $regularPay = ($hours * $basicPayHourlyRate) * $regularMultiplier;
                        if($isRestDay){$restPay = ($hours * $basicPayHourlyRate) * $restMultiplier;}

                    } else if($splitWorkHourType == WorkHourType::NIGHT){

                        $regularMultiplier = $splitBaseMultiplier;
                        $nightMultiplier = ($splitHourlyMultiplier - $splitRegularMultiplier);
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier - $splitBaseMultiplier - $nightMultiplier : 0);

                        $regularPay = ($hours * $basicPayHourlyRate) * $regularMultiplier;
                        $nightPay = ($hours * $basicPayHourlyRate) * $nightMultiplier;
                        if($isRestDay){$restPay = ($hours * $basicPayHourlyRate) * $restMultiplier;}
                    }

                    if(isset($assignedEarningsPayload[CompensationEnum::BASIC_PAY->value])){
                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['regular_pay'] += $regularPay;
                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['night_differential_pay'] += $nightPay;
                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['rest_day_pay'] += $restPay;

                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['total'] = (
                            $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['regular_pay'] +
                            $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['night_differential_pay'] +
                            $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['rest_day_pay']
                        );
                    }

                    /**
                     * Allowance is always available as long as there is a working hour
                     **/
                    $allowanceValue = (($splitActualPresent / 60) * $allowanceHourlyRate);

                    if(isset($assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value])){

                        $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['regular_pay'] += $allowanceValue;

                        $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['total'] =
                            $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['regular_pay'];
                    }

                    if($test || $debug){
                        $splitResults['work_splits'][] = [
                            'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                                $splitWorkHourType->label() . ' ' .
                                $salaryStatementAttendance->day_type->label() . ' ' .
                                ($isRestDay ? 'rest day' : 'non-rest day'),
                            //'detail_id' => $detailId,
                            //'proxy_model' => $proxyModel,
                            'ACTUAL_PRESENT' => $splitActualPresent,
                            'WORKED HOURS' => $hours,
                            'REGULAR MULTIPLIER' => $splitRegularMultiplier,
                            'NON-RESTMULTIPLIER' => $splitNonRestMultiplier,
                            'HOURLYR_MULTIPLIER' => $splitHourlyMultiplier,
                            'BASE_RA_MULTIPLIER' => $splitBaseMultiplier,
                            'BASIC' => ($hours * $basicPayHourlyRate),
                            ...($splitWorkHourType == WorkHourType::NIGHT ? ['NIGHT MULTIPLIER' => $nightMultiplier] : []),
                            ...($isRestDay ? ['REST MULTIPLIER' => $restMultiplier] : []),
                            '=>' => '=>',
                            'REGULAR_PAY' => $regularPay,
                            'NIGHT_DIFFERENTIAL_PAY' => $nightPay,
                            'REST_DAY_PAY' => $restPay,
                        ];
                    }

                    if(!$test){
                        //Update detail proxy model
                    }
                }

                foreach($this->overtimeSplits as $overtimeSplit){
                    if(!$test){$detailId = $workSplit['id'];$proxyModel = $workSplit['proxy_model'];}

                    $splitWorkHourType = $overtimeSplit['work_hour_type'];
                    $splitRegularMultiplier = $overtimeSplit['regular_rate_multiplier'];
                    $splitNonRestMultiplier = $overtimeSplit['non_rest_rate_multiplier'];

                    $splitHourlyMultiplier = $overtimeSplit['hourly_rate_multiplier'];
                    $splitBaseMultiplier = $overtimeSplit['base_rate_multiplier'];
                    $splitActualPresent = $overtimeSplit['actual_present'];

                    $regularPay = 0;$nightPay = 0;$restPay = 0;$hours = ($splitActualPresent / 60);

                    if($splitWorkHourType == WorkHourType::REGULAR){

                        $regularMultiplier = $splitBaseMultiplier;
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier - $splitNonRestMultiplier : 0);

                        $regularPay = ($hours * $basicPayHourlyRate) * $regularMultiplier;
                        if($isRestDay){$restPay = ($hours * $basicPayHourlyRate) * $restMultiplier;}

                    } else if($splitWorkHourType == WorkHourType::NIGHT){

                        $regularPay = ($hours * $basicPayHourlyRate) * $splitBaseMultiplier;
                        $nightMultiplier = ($splitHourlyMultiplier - $splitRegularMultiplier);
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier - $splitBaseMultiplier - $nightMultiplier : 0);

                        $nightPay = ($hours * $basicPayHourlyRate) * $nightMultiplier;
                        if($isRestDay){$restPay = ($hours * $basicPayHourlyRate) * $restMultiplier;}
                    }

                    if(isset($assignedEarningsPayload[CompensationEnum::OVERTIME->value])){
                        $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['regular_pay'] += $regularPay;
                        $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['night_differential_pay'] += $nightPay;
                        $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['rest_day_pay'] += $restPay;

                        $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['total'] = (
                            $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['regular_pay'] +
                            $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['night_differential_pay'] +
                            $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['rest_day_pay']
                        );
                    }

                    if($test || $debug){
                        $splitResults['overtime_splits'][] = [
                            'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                                $splitWorkHourType->label() . ' ' .
                                $salaryStatementAttendance->day_type->label() . ' ' .
                                ($isRestDay ? 'rest day' : 'non-rest day'),
                            //'detail_id' => $detailId,
                            //'proxy_model' => $proxyModel,
                            'ACTUAL_PRESENT' => $splitActualPresent,
                            'WORKED HOURS' => $hours,
                            'REGULAR MULTIPLIER' => $splitRegularMultiplier,
                            'NON-RESTMULTIPLIER' => $splitNonRestMultiplier,
                            'HOURLYR_MULTIPLIER' => $splitHourlyMultiplier,
                            'BASE_RA_MULTIPLIER' => $splitBaseMultiplier,
                            'BASIC' => ($hours * $basicPayHourlyRate),
                            ...($splitWorkHourType == WorkHourType::NIGHT ? ['NIGHT MULTIPLIER' => $nightMultiplier] : []),
                            ...($isRestDay ? ['REST MULTIPLIER' => $restMultiplier] : []),
                            '=>' => '=>',
                            'REGULAR_PAY' => $regularPay,
                            'NIGHT_DIFFERENTIAL_PAY' => $nightPay,
                            'REST_DAY_PAY' => $restPay,
                        ];
                    }

                    if(!$test){
                        //Update detail proxy model
                    }
                }
            }

            if($isHoliday){

                foreach($this->workSplits as $workSplit){
                    if(!$test){$detailId = $workSplit['id'];$proxyModel = $workSplit['proxy_model'];}

                    $splitWorkHourType = $workSplit['work_hour_type'];
                    $splitRegularMultiplier = $workSplit['regular_rate_multiplier'];
                    $splitNonRestMultiplier = $workSplit['non_rest_rate_multiplier'];

                    $splitHourlyMultiplier = $workSplit['hourly_rate_multiplier'];
                    $splitBaseMultiplier = $workSplit['base_rate_multiplier'];
                    $splitActualPresent = $workSplit['actual_present'];

                    $regularPay = 0;$nightPay = 0;$holidayPay = 0;$restPay = 0;$hours = ($splitActualPresent / 60);

                    if($splitWorkHourType == WorkHourType::REGULAR){

                        $regularMultiplier = $splitBaseMultiplier;
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier - $splitNonRestMultiplier : 0);
                        $holidayMultiplier = ($splitHourlyMultiplier - $splitBaseMultiplier - $restMultiplier);


                        $regularPay = ($hours * $basicPayHourlyRate) * $regularMultiplier;
                        $holidayPay = ($hours * $basicPayHourlyRate) * $holidayMultiplier;
                        if($isRestDay){$restPay = ($hours * $basicPayHourlyRate) * $restMultiplier;}

                    } else if($splitWorkHourType == WorkHourType::NIGHT){

                        $regularMultiplier = $splitBaseMultiplier;
                        $nightMultiplier = ($splitHourlyMultiplier - $splitRegularMultiplier);
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier - $splitNonRestMultiplier : 0);
                        $holidayMultiplier = ($splitHourlyMultiplier - $splitBaseMultiplier - $nightMultiplier - $restMultiplier);

                        $regularPay = ($hours * $basicPayHourlyRate) * $regularMultiplier;
                        $holidayPay = ($hours * $basicPayHourlyRate) * $holidayMultiplier;
                        $nightPay = ($hours * $basicPayHourlyRate) * $nightMultiplier;
                        if($isRestDay){$restPay = ($hours * $basicPayHourlyRate) * $restMultiplier;}
                    }

                    //Basic pay
                    if(isset($assignedEarningsPayload[CompensationEnum::BASIC_PAY->value])){

                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['regular_pay'] += $regularPay;
                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['night_differential_pay'] += $nightPay;
                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['rest_day_pay'] += $restPay;

                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['total'] = (
                            $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['regular_pay'] +
                            $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['night_differential_pay'] +
                            $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['rest_day_pay']
                        );
                    }

                    //Holiday pay
                    if(isset($globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value])){

                        $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['regular_pay'] += $holidayPay;

                        $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['total'] = (
                            $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['regular_pay'] +
                            $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['night_differential_pay']
                        );
                    }

                    /**
                     * Allowance is always available as long as there is a working hour
                     **/
                    $allowanceValue = (($splitActualPresent / 60) * $allowanceHourlyRate);

                    if(isset($assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value])){

                        $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['regular_pay'] += $allowanceValue;

                        $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['total'] =
                            $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['regular_pay'];
                    }

                    if($test || $debug){
                        $splitResults['work_splits'][] = [
                            'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                                $splitWorkHourType->label() . ' ' .
                                $salaryStatementAttendance->day_type->label() . ' ' .
                                ($isRestDay ? 'rest day' : 'non-rest day'),
                            //'detail_id' => $detailId,
                            //'proxy_model' => $proxyModel,
                            'ACTUAL_PRESENT' => $splitActualPresent,
                            'WORKED HOURS' => $hours,
                            'REGULAR MULTIPLIER' => $splitRegularMultiplier,
                            'NON-RESTMULTIPLIER' => $splitNonRestMultiplier,
                            'HOURLYR_MULTIPLIER' => $splitHourlyMultiplier,
                            'BASE_RA_MULTIPLIER' => $splitBaseMultiplier,
                            'BASIC' => ($hours * $basicPayHourlyRate),
                            ...($splitWorkHourType == WorkHourType::NIGHT ? ['NIGHT MULTIPLIER' => $nightMultiplier] : []),
                            ...($isRestDay ? ['REST MULTIPLIER' => $restMultiplier] : []),
                            'HOLIDAY MULTIPLIER' => $holidayMultiplier,
                            '=>' => '=>',
                            'REGULAR_PAY' => $regularPay,
                            'NIGHT_DIFFERENTIAL_PAY' => $nightPay,
                            'REST_DAY_PAY' => $restPay,
                            'HOLIDAY_PAY' => $holidayPay
                        ];
                    }

                    if(!$test){
                        //Update detail proxy model
                    }
                }

                foreach($this->overtimeSplits as $overtimeSplit){
                    if(!$test){$detailId = $workSplit['id'];$proxyModel = $workSplit['proxy_model'];}

                    $splitWorkHourType = $overtimeSplit['work_hour_type'];
                    $splitRegularMultiplier = $overtimeSplit['regular_rate_multiplier'];
                    $splitNonRestMultiplier = $overtimeSplit['non_rest_rate_multiplier'];

                    $splitHourlyMultiplier = $overtimeSplit['hourly_rate_multiplier'];
                    $splitBaseMultiplier = $overtimeSplit['base_rate_multiplier'];
                    $splitActualPresent = $overtimeSplit['actual_present'];

                    $regularPay = 0;$nightPay = 0;$holidayPay = 0;$restPay = 0;$hours = ($splitActualPresent / 60);

                    if($splitWorkHourType == WorkHourType::REGULAR){

                        $regularMultiplier = $splitBaseMultiplier;
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier - $splitNonRestMultiplier : 0);
                        $holidayMultiplier = ($splitHourlyMultiplier - $splitBaseMultiplier - $restMultiplier);

                        $regularPay = ($hours * $basicPayHourlyRate) * $regularMultiplier;
                        $holidayPay = ($hours * $basicPayHourlyRate) * $holidayMultiplier;
                        if($isRestDay){$restPay = ($hours * $basicPayHourlyRate) * $restMultiplier;}

                    } else if($splitWorkHourType == WorkHourType::NIGHT){

                        $regularMultiplier = $splitBaseMultiplier;
                        $nightMultiplier = ($splitHourlyMultiplier - $splitRegularMultiplier);
                        $restMultiplier = ($isRestDay ? $splitHourlyMultiplier - $splitNonRestMultiplier : 0);
                        $holidayMultiplier = ($splitHourlyMultiplier - $splitBaseMultiplier - $nightMultiplier - $restMultiplier);

                        $regularPay = ($hours * $basicPayHourlyRate) * $regularMultiplier;
                        $holidayPay = ($hours * $basicPayHourlyRate) * $holidayMultiplier;
                        $nightPay = ($hours * $basicPayHourlyRate) * $nightMultiplier;
                        if($isRestDay){$restPay = ($hours * $basicPayHourlyRate) * $restMultiplier;}
                    }

                    //Overtime pay
                    if(isset($assignedEarningsPayload[CompensationEnum::OVERTIME->value])){

                        $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['regular_pay'] += $regularPay;
                        $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['night_differential_pay'] += $nightPay;
                        $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['rest_day_pay'] += $restPay;

                        $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['total'] = (
                            $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['regular_pay'] +
                            $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['night_differential_pay'] +
                            $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['rest_day_pay']
                        );
                    }

                    //Holiday pay
                    if(isset($globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value])){

                        $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['regular_pay'] += $holidayPay;

                        $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['total'] = (
                            $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['regular_pay'] +
                            $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['night_differential_pay']
                        );
                    }

                    if($test || $debug){
                        $splitResults['overtime_splits'][] = [
                            'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                                $splitWorkHourType->label() . ' ' .
                                $salaryStatementAttendance->day_type->label() . ' ' .
                                ($isRestDay ? 'rest day' : 'non-rest day'),
                            //'detail_id' => $detailId,
                            //'proxy_model' => $proxyModel,
                            'ACTUAL_PRESENT' => $splitActualPresent,
                            'WORKED HOURS' => $hours,
                            'REGULAR MULTIPLIER' => $splitRegularMultiplier,
                            'NON-RESTMULTIPLIER' => $splitNonRestMultiplier,
                            'HOURLYR_MULTIPLIER' => $splitHourlyMultiplier,
                            'BASE_RA_MULTIPLIER' => $splitBaseMultiplier,
                            'BASIC' => ($hours * $basicPayHourlyRate),
                            ...($splitWorkHourType == WorkHourType::NIGHT ? ['NIGHT MULTIPLIER' => $nightMultiplier] : []),
                            ...($isRestDay ? ['REST MULTIPLIER' => $restMultiplier] : []),
                            'HOLIDAY MULTIPLIER' => $holidayMultiplier,
                            '=>' => '=>',
                            'REGULAR_PAY' => $regularPay,
                            'NIGHT_DIFFERENTIAL_PAY' => $nightPay,
                            'REST_DAY_PAY' => $restPay,
                            'HOLIDAY_PAY' => $holidayPay
                        ];
                    }

                    if(!$test){
                        //Update detail proxy model
                    }
                }
            }
        }

        if(!$isPresent && $payableNoneAttendance){

            foreach($this->workSplits as $workSplit){
                if(!$test){$detailId = $workSplit['id'];$proxyModel = $workSplit['proxy_model'];}

                $splitWorkHourType = $workSplit['work_hour_type'];
                $splitHourlyMultiplier = $workSplit['hourly_rate_multiplier'];
                $splitBaseMultiplier = $workSplit['base_rate_multiplier'];
                $splitSplitDuration = $workSplit['split_duration'];
                $splitActualPresent = $workSplit['actual_present'];
                $hourlyRate = $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['hourly_rate'] ?? 0;

                $regularPay = (($splitSplitDuration / 60) * $hourlyRate) * $splitBaseMultiplier;

                if(isset($globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value])){
                    $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['regular_pay'] += $regularPay;
                }

                if($test || $debug){
                    $splitResults['payable_non_attendance_work_splits'][] = [
                        'date' => $salaryStatementAttendance->date->toDateString() . ' ' .
                            $salaryStatementAttendance->day_type->label() . ' ' .
                            ($isRestDay ? 'rest day' : 'non-rest day'),
                        'work_hour_type' => $splitWorkHourType->label(),
                        //'detail_id' => $detailId,
                        //'proxy_model' => $proxyModel,
                        'HOURLYR_MULTIPLIER' => $splitHourlyMultiplier,
                        'BASE_RA_MULTIPLIER' => $splitBaseMultiplier,
                        'actual_present' => $splitActualPresent,
                        '=>' => '=>',
                        'REGULAR_PAY' => $regularPay,
                    ];
                }

                if(!$test){
                    //Update detail proxy model
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

    public function listSalaryStatementAttendanceStatusAndDayTypes(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        $isPresent = in_array($salaryStatementAttendance->status, [SalaryStatementAttendanceStatus::FULL_PRESENT, SalaryStatementAttendanceStatus::PRESENT_WITH_IRREGULARITIES]);
        $isLeave = in_array($salaryStatementAttendance->status, [SalaryStatementAttendanceStatus::LEAVE_WITHOUT_PAY, SalaryStatementAttendanceStatus::LEAVE_WITH_PAY]);
        $isHoliday = in_array($salaryStatementAttendance->day_type, [SalaryStatementAttendanceDayType::SPECIAL_HOLIDAY, SalaryStatementAttendanceDayType::LEGAL_HOLIDAY, SalaryStatementAttendanceDayType::DOUBLE_HOLIDAY]);

        $isRegularWorkingDay = $salaryStatementAttendance->day_type == SalaryStatementAttendanceDayType::WORKING_DAY;
        $isLegalHoliday = in_array($salaryStatementAttendance->day_type, [SalaryStatementAttendanceDayType::LEGAL_HOLIDAY, SalaryStatementAttendanceDayType::DOUBLE_HOLIDAY,]);
        $isSpecialHoliday = $salaryStatementAttendance->day_type == SalaryStatementAttendanceDayType::SPECIAL_HOLIDAY;

        $leaveWithoutPay = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::LEAVE_WITHOUT_PAY;
        $leaveWithPay = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::LEAVE_WITH_PAY;
        $leaveWithoutPayAndIsLegalHoliday = $leaveWithoutPay && $isLegalHoliday;

        $isAbsentAndLegalHoliday = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::ABSENT && $isLegalHoliday;
        $payableNoneAttendance = $leaveWithPay || $leaveWithoutPayAndIsLegalHoliday || $isAbsentAndLegalHoliday;

        return [
            $isPresent,
            $isLeave,
            $isHoliday,

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
    ){
        $hourlyRate = 0;

        if($payrollFrequency === PayFrequencyEnum::MONTHLY){

            $hourlyRate = match($amountablePayrollComponent->pay_period){
                PayPeriod::MONTHLY => ((float)$amountablePayrollComponent->amount / $this->frequencyWorkingDayCount) / ($totalWorkMinutes / 60),
                PayPeriod::SEMI_MONTHLY => (((float)$amountablePayrollComponent->amount * 2) / $this->frequencyWorkingDayCount) / ($totalWorkMinutes / 60),
                PayPeriod::DAILY => (float)$amountablePayrollComponent->amount / ($totalWorkMinutes / 60),

                //Return if hourly
                default => $amountablePayrollComponent->amount
            };
        }

        if($payrollFrequency === PayFrequencyEnum::SEMI_MONTHLY){

            $hourlyRate = match($amountablePayrollComponent->pay_period){
                PayPeriod::MONTHLY => (((float)$amountablePayrollComponent->amount / 2) / $this->frequencyWorkingDayCount ) / ($totalWorkMinutes / 60),
                PayPeriod::SEMI_MONTHLY => ((float)$amountablePayrollComponent->amount / $this->frequencyWorkingDayCount) / ($totalWorkMinutes / 60),
                PayPeriod::DAILY => (float)$amountablePayrollComponent->amount / ($totalWorkMinutes / 60),

                //Return if hourly
                default => $amountablePayrollComponent->amount
            };
        }

        return $hourlyRate;
    }
}
