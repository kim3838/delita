<?php

namespace App\Blueprint;

use App\Models\Attendance;

interface AttendanceSplitterInterface
{
    public function generate(Attendance $attendance, $test = false, $debug = false): bool | array;
}
