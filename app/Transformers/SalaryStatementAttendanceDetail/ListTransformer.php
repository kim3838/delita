<?php

namespace App\Transformers\SalaryStatementAttendanceDetail;

use App\Facades\MoneyFormat;
use App\Helpers\TimeHelper;
use App\Models\SalaryStatementAttendanceDetail;
use Brick\Math\BigDecimal;
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

            'hourly_rate' => MoneyFormat::numberFormat($salaryStatementAttendanceDetail->hourly_rate, 4),
            'actual_present' => $salaryStatementAttendanceDetail->actual_present,

            'regular_pay' => $regularPay->isZero() ? '--' : MoneyFormat::numberFormat($regularPay, 4),
            'allowance' => $allowance->isZero() ? '--' : MoneyFormat::numberFormat($allowance, 4),
            'night_differential_pay' => $nightDifferentialPay->isZero() ? '--' : MoneyFormat::numberFormat($nightDifferentialPay, 4),
            'rest_day_pay' => $restDayPay->isZero() ? '--' : MoneyFormat::numberFormat($restDayPay, 4),
            'leave_pay' => $leavePay->isZero() ? '--' : MoneyFormat::numberFormat($leavePay, 4),
            'holiday_pay' => $holidayPay->isZero() ? '--' : MoneyFormat::numberFormat($holidayPay, 4),

            'holiday_pay_forfeited' => boolval($salaryStatementAttendanceDetail->holiday_pay_forfeited),
        ];
    }
}
