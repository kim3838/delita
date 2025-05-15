<?php

namespace App\Enums;

enum Gender: int implements BaseEnum
{
    case NOT_SPECIFIED = 0;
    case MALE = 1;
    case FEMALE = 2;

    public function label(): string
    {
        return match ($this) {
            self::NOT_SPECIFIED => "Not Specified",
            self::MALE => "Male",
            self::FEMALE => "Female",
        };
    }
}
