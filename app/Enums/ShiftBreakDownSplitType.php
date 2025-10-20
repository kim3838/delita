<?php

namespace App\Enums;

enum ShiftBreakDownSplitType: int implements BaseEnum
{
    case WORK = 0;
    case LUNCH = 1;
    case OVERTIME = 2;

    public function label(): string
    {
        return match ($this) {
            self::WORK => 'Work',
            self::LUNCH => 'Lunch',
            self::OVERTIME => 'Overtime',
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
