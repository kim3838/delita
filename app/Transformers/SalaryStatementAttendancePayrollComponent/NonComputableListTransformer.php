<?php

namespace App\Transformers\SalaryStatementAttendancePayrollComponent;

use App\Models\SalaryStatementAttendancePayrollComponent;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class NonComputableListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendancePayrollComponent $salaryStatementAttendancePayrollComponent): array
    {
        $regularPay = BigDecimal::of($salaryStatementAttendancePayrollComponent->regular_pay);
        $nightDifferentialPay = BigDecimal::of($salaryStatementAttendancePayrollComponent->night_differential_pay);
        $restDayPay = BigDecimal::of($salaryStatementAttendancePayrollComponent->rest_day_pay);
        $total = BigDecimal::of($salaryStatementAttendancePayrollComponent->total);

        return [
            'row_number' => $salaryStatementAttendancePayrollComponent->row_number,
            'id' => $salaryStatementAttendancePayrollComponent->id,
            'formulable_type' => $salaryStatementAttendancePayrollComponent->formulable_type?->toArray(),
            'component_type' => $salaryStatementAttendancePayrollComponent->component_type?->toArray(),

            'component_sub_type' => $salaryStatementAttendancePayrollComponent->component_sub_type,
            'component_name' => $salaryStatementAttendancePayrollComponent->component_name,

            'regular_pay' => $regularPay->isZero() ? '--' : $regularPay->toScale(4, RoundingMode::HalfUp),
            'night_differential_pay' => $nightDifferentialPay->isZero() ? '--' : $nightDifferentialPay->toScale(4, RoundingMode::HalfUp),
            'rest_day_pay' => $restDayPay->isZero() ? '--' : $restDayPay->toScale(4, RoundingMode::HalfUp),
            'total' => $total->isZero() ? '--' : $total->toScale(4, RoundingMode::HalfUp),
        ];
    }
}
