<?php

namespace App\Concrete;

use Brick\Math\BigDecimal;
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

    public function numberFormat(BigNumber|int|float|string $amount, int $scale = 2): string
    {
        $number = BigDecimal::of($amount)->toScale($scale, RoundingMode::HalfUp);

        return number_format($number->toFloat(), $scale);
    }

    public function numberFormatComponentValue(null|array $item, $scale = 2)
    {
        if (is_null($item)) return null;

        foreach ($item as $key => $value) {

            if ($key === 'type') continue;

            if (is_array($value)) {

                $item[$key] = self::numberFormatComponentValue($value, $scale);

            } elseif (is_string($value) && is_numeric($value)) {

                $item[$key] = self::numberFormat($value, $scale);
            }
        }
        return $item;
    }
}
