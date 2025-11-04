<?php

namespace App\Enums;

enum ShiftHolidayPolicy: int implements BaseEnum
{
    case DAY_OFF = 100;
    case ATTENDANCE_REQUIRED = 200;

    public function label(): string
    {
        return match ($this) {
            self::DAY_OFF => 'Day off',
            self::ATTENDANCE_REQUIRED => 'Attendance required',
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
