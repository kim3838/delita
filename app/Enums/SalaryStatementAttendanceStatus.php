<?php

namespace App\Enums;

enum SalaryStatementAttendanceStatus: int implements BaseEnum
{
    case TO_BE_DETERMINED = 0;
    case FULL_PRESENT = 100;
    case PRESENT_WITH_IRREGULARITIES = 103;
    case DAY_OFF = 200;
    case LEAVE_WITHOUT_PAY = 300;
    case LEAVE_WITH_PAY = 301;
    case LEAVE_BUT_CANT_IDENTIFY_IF_PAID_OR_NOT = 302;
    case ABSENT = 401;

    public function label(): string
    {
        return match ($this) {
            self::TO_BE_DETERMINED => 'To be determined',
            self::FULL_PRESENT => 'Full present',
            self::PRESENT_WITH_IRREGULARITIES => 'Present with irregularities',
            self::DAY_OFF => 'Day off',
            self::LEAVE_WITHOUT_PAY => 'Leave without pay',
            self::LEAVE_WITH_PAY => 'Leave with pay',
            self::LEAVE_BUT_CANT_IDENTIFY_IF_PAID_OR_NOT => "Leave but can't identify if paid or not",
            self::ABSENT => 'Absent',
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
