<?php

namespace App\Enums;

enum LeaveIntervalSpanType: int implements BaseEnum
{
    case DAY = 100;
    case MONTH = 200;
    case YEAR = 300;

    public function label(): string
    {
        return match ($this) {
            self::DAY => 'Day',
            self::MONTH => 'Month',
            self::YEAR => 'Year',
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
