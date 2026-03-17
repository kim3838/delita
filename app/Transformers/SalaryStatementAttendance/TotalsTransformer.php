<?php

namespace App\Transformers\SalaryStatementAttendance;

use App\Models\Hydrations\SalaryStatementAttendanceTotals;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class TotalsTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendanceTotals $salaryStatementAttendanceTotals): array
    {
        $totalRegularPay = BigDecimal::of($salaryStatementAttendanceTotals->regular_pay);
        $totalNightDifferentialPay = BigDecimal::of($salaryStatementAttendanceTotals->night_differential_pay);
        $totalRestDayPay = BigDecimal::of($salaryStatementAttendanceTotals->rest_day_pay);
        $total = BigDecimal::of($salaryStatementAttendanceTotals->total);

        return [
            'regular_pay' => $totalRegularPay->toScale(4, RoundingMode::HalfUp),
            'night_differential_pay' => $totalNightDifferentialPay->toScale(4, RoundingMode::HalfUp),
            'rest_day_pay' => $totalRestDayPay->toScale(4, RoundingMode::HalfUp),
            'total' => $total->toScale(4, RoundingMode::HalfUp),
        ];
    }
}
