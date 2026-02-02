<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterface;

trait HasTime
{
    protected function getNextDayIfNeeded(Carbon $date, string $time, Carbon $reference): Carbon
    {
        $carbon = $date->copy()->setTimeFromTimeString($time);

        // If the time is before the reference time, assume it's the next day
        if ($carbon->lte($reference)) {
            $carbon->addDay();
        }

        return $carbon;
    }

    protected function formatEndTime(Carbon $dateTime): string
    {
        // If it's exactly midnight (00:00), show as 24:00 of previous day
        if ($dateTime->format('H:i') === '00:00') {
            return '24:00';
        }

        return $dateTime->format('H:i');
    }

    protected function formatDuration(Carbon $start, Carbon $end): string
    {
        // Get total minutes difference
        $minutes = $start->diffInMinutes($end);

        // Convert minutes to HH:MM format
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    protected function getDateIfGteMinimum(Carbon $date, ?Carbon $minimum): Carbon
    {
        return !empty($minimum) && $date->lt($minimum)
            ? $minimum
            : $date;
    }

    protected function diffForHumans(Carbon $date, Carbon $now): string
    {
        $diffInSeconds = $date->diffInSeconds($now);

        $diffForHumans = $diffInSeconds > 0 && $diffInSeconds < 60
            ? 'just now'
            : $date->diffForHumans($now,[
                    'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                    'short' => true,
                ]
            );

        return $diffForHumans;
    }

    protected function getPreviousWeekDay(Carbon $date, int $weekday, int $nth = 1): Carbon
    {
        return $date->copy()
            ->previous($weekday)
            ->subWeeks($nth - 1);
    }

    protected function getPreviousNthIncludingCurrent(Carbon $date, int $nth = 1): Carbon
    {
        $date = $date->copy();

        return $date->day >= $nth
            ? $date->day($nth)
            : $date->subMonth()->day($nth);
    }

    protected function getNextNthIncludingCurrent(Carbon $date, int $nth = 1): Carbon
    {
        $date = $date->copy();

        return $date->day <= $nth
            ? $date->day($nth)
            : $date->addMonth()->day($nth);
    }
}
