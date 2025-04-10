<?php

namespace App\Enums;

enum AccountType: int implements BaseEnum
{
    case STANDARD = 0;
    case CORPORATE = 1;

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::CORPORATE => 'Corporate'
        };
    }
}
