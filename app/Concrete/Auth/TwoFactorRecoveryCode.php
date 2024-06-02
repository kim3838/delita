<?php

namespace App\Concrete\Auth;

use Illuminate\Support\Str;

class TwoFactorRecoveryCode
{
    /**
     * Generate a new recovery code.
     *
     * @return string
     */
    public static function generate()
    {
        return strtoupper(Str::random(10).'-'.Str::random(10));
    }
}
