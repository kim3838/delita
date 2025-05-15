<?php

namespace App\Enums;

enum MaritalStatus: int implements BaseEnum
{
    case NOT_SPECIFIED = 0;
    case SINGLE = 1;
    case MARRIED = 2;
    case WIDOWED = 3;
    case DIVORCED = 4;
    case SEPARATED = 5;

    public function label(): string
    {
        return match ($this) {
            self::NOT_SPECIFIED => "Not Specified",
            self::SINGLE => "Single",
            self::MARRIED => "Married",
            self::WIDOWED => "Widowed",
            self::DIVORCED => "Divorced",
            self::SEPARATED => "Separated",
        };
    }
}
