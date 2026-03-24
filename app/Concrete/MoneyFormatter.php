<?php

namespace App\Concrete;

use Brick\Math\BigNumber;
use Brick\Math\RoundingMode;
use Brick\Money\Context\CustomContext;
use Brick\Money\Money;

class MoneyFormatter
{
    public function toLocale(BigNumber|int|float|string $amount, string $currencyCode, int $scale = 2): string
    {
        $money = Money::of($amount, $currencyCode, roundingMode: RoundingMode::HalfUp)
            ->to(new CustomContext(scale: $scale));

        return $money->formatToLocale(config('app.locale'));
    }
}
