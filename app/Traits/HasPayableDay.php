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
        &$globalEarningsPayload
    ){
        list($isPresent, $isLeave, $isHoliday, $isLegalHoliday, $isSpecialHoliday, $leaveWithoutPay, $leaveWithPay, $leaveWithoutPayAndIsLegalHoliday, $isAbsentAndLegalHoliday, $payableNoneAttendance) = $this->listSalaryStatementAttendanceStatusAndDayTypes($salaryStatementAttendance);

        if($isPresent){

            foreach($this->workSplits as $workSplit){
                $splitWorkHourType = $workSplit['work_hour_type'];
                $splitRegularMultiplier = $workSplit['regular_rate_multiplier'];

                $splitMultiplier = $workSplit['hourly_rate_multiplier'];
                $splitBaseMultiplier = $workSplit['base_rate_multiplier'];
                $splitActualPresent = $workSplit['actual_present'];
                $basicPayHourlyRate = $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['hourly_rate'] ?? 0;
                $allowanceHourlyRate = $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['hourly_rate'] ?? 0;

                $allowanceValue = (($splitActualPresent / 60) * $allowanceHourlyRate) * $splitMultiplier;

                if($isLegalHoliday){

                    $basicPayValue = (($splitActualPresent / 60) * $basicPayHourlyRate) * $splitBaseMultiplier;

                    //Basic pay
                    if(isset($assignedEarningsPayload[CompensationEnum::BASIC_PAY->value])){
                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['total_amount'] += $basicPayValue;
                    }

                    $holidayPayValue = (($splitActualPresent / 60) * $basicPayHourlyRate) * ($splitMultiplier - $splitBaseMultiplier);

                    //Holiday pay
                    if(isset($globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value])){
                        $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['total_amount'] += $holidayPayValue;
                    }
                } else {

                    $basicPayValue = (($splitActualPresent / 60) * $basicPayHourlyRate) * $splitMultiplier;

                    //Basic pay
                    if(isset($assignedEarningsPayload[CompensationEnum::BASIC_PAY->value])){
                        $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['total_amount'] += $basicPayValue;
                    }
                }

                //Allowance
                if(isset($assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value])){
                    $assignedEarningsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['total_amount'] += $allowanceValue;
                }
            }

            foreach($this->overtimeSplits as $overtimeSplit){
                $splitWorkHourType = $overtimeSplit['work_hour_type'];
                $splitRegularMultiplier = $overtimeSplit['regular_rate_multiplier'];

                $splitMultiplier = $overtimeSplit['hourly_rate_multiplier'];
                $splitBaseMultiplier = $overtimeSplit['base_rate_multiplier'];
                $splitActualPresent = $overtimeSplit['actual_present'];
                $basicPayHourlyRate = $assignedEarningsPayload[CompensationEnum::BASIC_PAY->value]['hourly_rate'] ?? 0;

                $regularValue = 0;
                $nightValue = 0;

                if($splitWorkHourType == WorkHourType::REGULAR){

                    $regularValue = (($splitActualPresent / 60) * $basicPayHourlyRate) * $splitMultiplier;

                } else if($splitWorkHourType == WorkHourType::NIGHT){

                    $regularValue = (($splitActualPresent / 60) * $basicPayHourlyRate) * $splitRegularMultiplier;
                    $nightValue = (($splitActualPresent / 60) * $basicPayHourlyRate) * ($splitMultiplier - $splitRegularMultiplier);
                }

                if(isset($assignedEarningsPayload[CompensationEnum::OVERTIME->value])){
                    $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['regular_amount'] += $regularValue;
                    $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['night_amount'] += $nightValue;
                    $assignedEarningsPayload[CompensationEnum::OVERTIME->value]['total_amount'] += 0;
                }
            }
        }

        if($payableNoneAttendance){

            foreach($this->workSplits as $workSplit){
                $splitMultiplier = $workSplit['hourly_rate_multiplier'];
                $splitBaseMultiplier = $workSplit['base_rate_multiplier'];
                $splitSplitDuration = $workSplit['split_duration'];
                $splitActualPresent = $workSplit['actual_present'];
                $hourlyRate = $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['hourly_rate'] ?? 0;

                $value = (($splitSplitDuration / 60) * $hourlyRate) * $splitBaseMultiplier;

                if(isset($globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value])){
                    $globalEarningsPayload[CompensationEnum::HOLIDAY_PAY->value]['total_amount'] += $value;
                }
            }
        }
    }

    public function listSalaryStatementAttendanceStatusAndDayTypes(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        $isPresent = in_array($salaryStatementAttendance->status, [SalaryStatementAttendanceStatus::FULL_PRESENT, SalaryStatementAttendanceStatus::PRESENT_WITH_IRREGULARITIES]);
        $isLeave = in_array($salaryStatementAttendance->status, [SalaryStatementAttendanceStatus::LEAVE_WITHOUT_PAY, SalaryStatementAttendanceStatus::LEAVE_WITH_PAY]);
        $isHoliday = in_array($salaryStatementAttendance->day_type, [SalaryStatementAttendanceDayType::SPECIAL_HOLIDAY, SalaryStatementAttendanceDayType::LEGAL_HOLIDAY, SalaryStatementAttendanceDayType::DOUBLE_HOLIDAY]);

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
