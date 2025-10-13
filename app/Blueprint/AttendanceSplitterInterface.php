<?php

namespace App\Blueprint;

interface AttendanceSplitterInterface
{
    public function generate(array $attendance, $test = false, $debug = false): bool | array;
}
