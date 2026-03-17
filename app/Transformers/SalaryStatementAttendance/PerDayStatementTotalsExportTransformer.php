<?php

namespace App\Transformers\SalaryStatementAttendance;

use App\Models\SalaryStatementAttendance;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class PerDayStatementTotalsExportTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        $regularPay = BigDecimal::of($salaryStatementAttendance->regular_pay ?? '0.00');
        $nightDifferentialPay = BigDecimal::of($salaryStatementAttendance->night_differential_pay ?? '0.00');
        $restDayPay = BigDecimal::of($salaryStatementAttendance->rest_day_pay ?? '0.00');
        $total = BigDecimal::of($salaryStatementAttendance->total ?? '0.00');

        return [
            'employee_number' => $salaryStatementAttendance->employee_number,
            'employee_full_name' => $salaryStatementAttendance->employee_full_name,

            'date' => $salaryStatementAttendance->date?->toDateString(),
            'date_readable' => $salaryStatementAttendance->date?->format('M d, Y'),
            'week_day_name' => $salaryStatementAttendance->date?->format('l'),
            'status' => $salaryStatementAttendance->status?->label(),
            'day_type' => $salaryStatementAttendance->day_type?->label(),

            'formulable_type' => $salaryStatementAttendance->formulable_type?->label(),
            'component_type' => $salaryStatementAttendance->component_type?->label(),
            'component_name' => $salaryStatementAttendance->component_name,

            'regular_pay' => $regularPay->toScale(4, RoundingMode::HalfUp)->toString(),
            'night_differential_pay' => $nightDifferentialPay->toScale(4, RoundingMode::HalfUp)->toString(),
            'rest_day_pay' => $restDayPay->toScale(4, RoundingMode::HalfUp)->toString(),
            'total' => $total->toScale(4, RoundingMode::HalfUp)->toString(),
        ];
    }
}
