<?php

namespace App\Facades;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static localToGlobal(null | string | Carbon $localTime, string $format = 'Y-m-d'): ?string
 * @method static globalToLocal(null | string | Carbon $globalTime, string $format = 'Y-m-d'): ?string
 */
class TimeZoneConverterFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'time_zone_converter';
    }
}
