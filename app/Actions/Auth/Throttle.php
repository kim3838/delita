<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Throttle
{
    public static function key(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('identifier')) . '|' . $request->ip());
    }
}
