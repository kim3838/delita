<?php

namespace App\Enums;

enum PayrollAttendanceStatus: int implements BaseEnum
{
    case TO_BE_DETERMINED = 0;
    case FULL_PRESENT = 100;
    case PRESENT_WITH_IRREGULARITIES = 103;
    case DAY_OFF = 200;
    case LEAVE = 300;
    case ABSENT = 301;

    public function label(): string
    {
        return match ($this) {
            self::TO_BE_DETERMINED => 'To be determined',
            self::FULL_PRESENT => 'Full present',
            self::PRESENT_WITH_IRREGULARITIES => 'Present with irregularities',
            self::DAY_OFF => 'Day off',
            self::LEAVE => 'Leave',
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
