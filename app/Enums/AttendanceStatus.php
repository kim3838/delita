<?php

namespace App\Enums;

enum AttendanceStatus: int implements BaseEnum
{
    case NOT_SPECIFIED = 0;
    case FULL_PRESENT = 100;
    case PRESENT_WITH_IRREGULARITIES = 103;
    case ABSENT = 200;

    public function label(): string
    {
        return match ($this) {
            self::NOT_SPECIFIED => 'Not specified',
            self::FULL_PRESENT => 'Full present',
            self::PRESENT_WITH_IRREGULARITIES => 'Present with irregularities',
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
