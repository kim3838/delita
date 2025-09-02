<?php

namespace App\Concrete;

use Carbon\Carbon;

class TimeZoneConverter
{
    public static function localToGlobal(null | string | Carbon $localTime, string $format = 'Y-m-d'): ?string
    {
        if(!$localTime){
            return null;
        }

        $userTimezone = auth()->user()?->timezone ?? config('app.timezone');

        $localTime = $localTime instanceof Carbon
            ? $localTime->format($format)
            : $localTime;

        $localTime = Carbon::createFromFormat($format, $localTime, $userTimezone);

        $globalTime = $localTime->setTimezone(config('app.timezone'));

        return $globalTime->format($format);
    }

    public static function globalToLocal(null | string | Carbon $globalTime, string $format = 'Y-m-d'): ?string
    {
        if(!$globalTime){
            return null;
        }

        $userTimezone = auth()->user()?->timezone ?? config('app.timezone');

        $globalTime = $globalTime instanceof Carbon
            ? $globalTime
            : Carbon::createFromFormat($format, $globalTime, config('app.timezone'));

        $localTime = $globalTime->setTimezone($userTimezone);

        return $localTime->format($format);
    }
}
