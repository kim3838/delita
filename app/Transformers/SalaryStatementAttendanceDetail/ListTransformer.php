<?php

namespace App\Transformers\SalaryStatementAttendanceDetail;

use App\Helpers\TimeHelper;
use App\Models\SalaryStatementAttendanceDetail;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendanceDetail $salaryStatementAttendanceDetail): array
    {
        $regularPay = BigDecimal::of($salaryStatementAttendanceDetail->regular_pay);
        $allowance = BigDecimal::of($salaryStatementAttendanceDetail->allowance);
        $nightDifferentialPay = BigDecimal::of($salaryStatementAttendanceDetail->night_differential_pay);
        $restDayPay = BigDecimal::of($salaryStatementAttendanceDetail->rest_day_pay);
        $leavePay = BigDecimal::of($salaryStatementAttendanceDetail->leave_pay);
        $holidayPay = BigDecimal::of($salaryStatementAttendanceDetail->holiday_pay);

        return [
            'row_number' => $salaryStatementAttendanceDetail->row_number,
            'id' => $salaryStatementAttendanceDetail->model_id . '.' . $salaryStatementAttendanceDetail->model_alias,
            'salary_statement_attendance_id' => $salaryStatementAttendanceDetail->salary_statement_attendance_id,
            'date' => $salaryStatementAttendanceDetail->date->toDateString(),
            'split_type' => $salaryStatementAttendanceDetail->split_type?->toArray(),
            'split_start' => $salaryStatementAttendanceDetail->split_start,
            'split_end' => $salaryStatementAttendanceDetail->split_end,
            'split_duration' => TimeHelper::minutesToTime($salaryStatementAttendanceDetail->split_duration),
            'order' => $salaryStatementAttendanceDetail->order,

            'work_hour_type' => $salaryStatementAttendanceDetail->work_hour_type?->toArray(),
            'hourly_rate_type' => $salaryStatementAttendanceDetail->hourly_rate_type?->toArray(),

            'hourly_rate' => BigDecimal::of($salaryStatementAttendanceDetail->hourly_rate)->toScale(4, RoundingMode::HalfUp),
            'actual_present' => $salaryStatementAttendanceDetail->actual_present,

            'regular_pay' => $regularPay->isZero() ? '--' : $regularPay->toScale(4, RoundingMode::HalfUp),
            'allowance' => $allowance->isZero() ? '--' : $allowance->toScale(4, RoundingMode::HalfUp),
            'night_differential_pay' => $nightDifferentialPay->isZero() ? '--' : $nightDifferentialPay->toScale(4, RoundingMode::HalfUp),
            'rest_day_pay' => $restDayPay->isZero() ? '--' : $restDayPay->toScale(4, RoundingMode::HalfUp),
            'leave_pay' => $leavePay->isZero() ? '--' : $leavePay->toScale(4, RoundingMode::HalfUp),
            'holiday_pay' => $holidayPay->isZero() ? '--' : $holidayPay->toScale(4, RoundingMode::HalfUp),

            'holiday_pay_forfeited' => boolval($salaryStatementAttendanceDetail->holiday_pay_forfeited),
        ];
    }
}
