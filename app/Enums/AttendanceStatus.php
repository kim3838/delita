<?php

namespace App\Enums;

enum AttendanceStatus: int implements BaseEnum
{
    case PRESENT = 0;
    case ABSENT = 1;
    case INCOMPLETE = 2;

    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'Present',
            self::ABSENT => 'Absent',
            self::INCOMPLETE => 'Incomplete',
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
