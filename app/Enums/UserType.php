<?php

namespace App\Enums;

enum UserType: int implements BaseEnum
{
    case DEFAULT = 0;
    case ADMIN = 1;
    case SUPER_ADMIN = 2;

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin'
        };
    }
}
