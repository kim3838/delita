<?php

namespace App\Enums;

enum LeavePeriodType: int implements BaseEnum
{
    case INTERVAL = 100;
    case CALENDAR_YEAR = 200;

    public function label(): string
    {
        return match ($this) {
            self::INTERVAL => 'Interval',
            self::CALENDAR_YEAR => 'Calendar year',
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
