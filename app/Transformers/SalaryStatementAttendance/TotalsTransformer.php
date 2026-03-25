<?php

namespace App\Transformers\SalaryStatementAttendance;

use App\Facades\MoneyFormat;
use App\Models\Hydrations\SalaryStatementAttendanceTotals;
use Brick\Math\BigDecimal;
use League\Fractal\TransformerAbstract;

class TotalsTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendanceTotals $salaryStatementAttendanceTotals): array
    {
        $totalRegularPay = BigDecimal::of($salaryStatementAttendanceTotals->regular_pay ?? 0);
        $totalNightDifferentialPay = BigDecimal::of($salaryStatementAttendanceTotals->night_differential_pay ?? 0);
        $totalRestDayPay = BigDecimal::of($salaryStatementAttendanceTotals->rest_day_pay ?? 0);
        $total = BigDecimal::of($salaryStatementAttendanceTotals->total ?? 0);

        return [
            'regular_pay' => MoneyFormat::toLocale($totalRegularPay, $salaryStatementAttendanceTotals->company_currency_code),
            'night_differential_pay' => MoneyFormat::toLocale($totalNightDifferentialPay, $salaryStatementAttendanceTotals->company_currency_code),
            'rest_day_pay' => MoneyFormat::toLocale($totalRestDayPay, $salaryStatementAttendanceTotals->company_currency_code),
            'total' => MoneyFormat::toLocale($total, $salaryStatementAttendanceTotals->company_currency_code),
        ];
    }
}
