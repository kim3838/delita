<?php

namespace App\Transformers\SalaryStatementAttendance;

use App\Models\Employee;
use App\Models\SalaryStatementAttendance;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        $employee = Employee::query()->find($salaryStatementAttendance->employee_id);

        $regularPay = BigDecimal::of($salaryStatementAttendance->regular_pay ?? '0.00');
        $nightDifferentialPay = BigDecimal::of($salaryStatementAttendance->night_differential_pay ?? '0.00');
        $restDayPay = BigDecimal::of($salaryStatementAttendance->rest_day_pay ?? '0.00');
        $total = BigDecimal::of($salaryStatementAttendance->total ?? '0.00');

        return [
            'row_number' => $salaryStatementAttendance->row_number,
            'id' => $salaryStatementAttendance->id,

            'employee_number' => $employee->number,
            'employee_full_name' => $employee->full_name,

            'date' => $salaryStatementAttendance->date?->format('F j, Y'),
            'week_day_name' => $salaryStatementAttendance->date?->format('l'),
            'status' => $salaryStatementAttendance->status?->toArray(),
            'day_type' => $salaryStatementAttendance->day_type?->toArray(),

            'formulable_type' => $salaryStatementAttendance->formulable_type?->toArray(),
            'component_type' => $salaryStatementAttendance->component_type?->toArray(),
            'component_name' => $salaryStatementAttendance->component_name,

            'regular_pay' => $regularPay->toScale(2, RoundingMode::HalfUp)->toString(),
            'night_differential_pay' => $nightDifferentialPay->toScale(2, RoundingMode::HalfUp)->toString(),
            'rest_day_pay' => $restDayPay->toScale(2, RoundingMode::HalfUp)->toString(),
            'total' => $total->toScale(2, RoundingMode::HalfUp)->toString(),
        ];
    }
}
