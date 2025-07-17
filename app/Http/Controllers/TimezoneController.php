<?php

namespace App\Http\Controllers;

use App\Facades\ResponseJson;
use DateTimeZone;

class TimezoneController extends Controller
{
    public function selection()
    {
        $timezones = DateTimeZone::listIdentifiers();

        $formatted = array_map(function($tz) {
            return [
                'text' => $tz,
                'value' => $tz
            ];
        }, $timezones);

        return ResponseJson::successfulResponse([
            'selection' => $formatted
        ]);
    }
}
