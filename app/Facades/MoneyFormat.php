<?php

namespace App\Facades;

use Brick\Math\BigNumber;
use Illuminate\Support\Facades\Facade;

/**
 * @method static toLocale(BigNumber|int|float|string $amount, string $currencyCode, int $scale = 2): string
 **/
class MoneyFormat extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'money_format';
    }
}
