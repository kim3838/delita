<?php

namespace App\Transformers\SalaryStatementAttendance;

use App\Facades\MoneyFormat;
use App\Models\SalaryStatementAttendance;
use Brick\Math\BigDecimal;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        $regularPay = BigDecimal::of($salaryStatementAttendance->regular_pay ?? '0.00');
        $nightDifferentialPay = BigDecimal::of($salaryStatementAttendance->night_differential_pay ?? '0.00');
        $restDayPay = BigDecimal::of($salaryStatementAttendance->rest_day_pay ?? '0.00');
        $total = BigDecimal::of($salaryStatementAttendance->total ?? '0.00');

        return [
            'row_number' => $salaryStatementAttendance->row_number,
            'id' => $salaryStatementAttendance->id,

            'employee_number' => $salaryStatementAttendance->employee_number,
            'employee_full_name' => $salaryStatementAttendance->employee_full_name,

            'date' => $salaryStatementAttendance->date?->toDateString(),
            'date_readable' => $salaryStatementAttendance->date?->format('M d, Y'),
            'week_day_name' => $salaryStatementAttendance->date?->format('l'),
            'status' => $salaryStatementAttendance->status?->toArray(),
            'day_type' => $salaryStatementAttendance->day_type?->toArray(),

            'formulable_type' => $salaryStatementAttendance->formulable_type?->toArray(),
            'component_type' => $salaryStatementAttendance->component_type?->toArray(),
            'component_name' => $salaryStatementAttendance->component_name,

            'regular_pay' => MoneyFormat::numberFormat($regularPay, 4),
            'night_differential_pay' => MoneyFormat::numberFormat($nightDifferentialPay, 4),
            'rest_day_pay' => MoneyFormat::numberFormat($restDayPay, 4),
            'total' => MoneyFormat::numberFormat($total, 4),
        ];
    }
}
