<?php

namespace App\Helpers;

class TimeHelper
{
    public static function timeToMinutes($time): int
    {
        if (empty($time)) {
            return 0;
        }

        // Ensure we have a string
        $time = (string)$time;

        // Split by colon
        $parts = explode(':', $time);

        if (count($parts) !== 2) {
            return 0;
        }

        $hours = (int)$parts[0];
        $minutes = (int)$parts[1];

        return ($hours * 60) + $minutes;
    }

    public static function minutesToTime($minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
