<?php

namespace App\Enums;

enum CompanyUserAssignmentType: int implements BaseEnum
{
    case DEFAULT = 0;
    case ADMIN = 1;

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
            self::ADMIN => 'Admin',
        };
    }
}
