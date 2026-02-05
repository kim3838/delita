<?php

namespace App\Enums;

enum SalaryStatementAttendanceDayType: int implements BaseEnum
{
    case WORKING_DAY = 100;
    case DAY_OFF = 200;
    case SPECIAL_HOLIDAY = 700;
    case LEGAL_HOLIDAY = 800;
    case DOUBLE_HOLIDAY = 900;

    public function label(): string
    {
        return match ($this) {
            self::WORKING_DAY => 'Working day',
            self::DAY_OFF => 'Day off',
            self::SPECIAL_HOLIDAY => 'Special holiday',
            self::LEGAL_HOLIDAY => 'Legal holiday',
            self::DOUBLE_HOLIDAY => 'Double holiday',
        };
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'text' => $this->label(),
        ];
    }

    public static function all(): array
    {
        return array_map(
            fn(self $case) => $case->toArray(),
            self::cases()
        );
    }
}
