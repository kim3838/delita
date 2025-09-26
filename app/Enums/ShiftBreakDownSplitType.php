<?php

namespace App\Enums;

enum ShiftBreakDownSplitType: int implements BaseEnum
{
    case WORK = 0;
    case LUNCH = 1;

    public function label(): string
    {
        return match ($this) {
            self::WORK => 'Work',
            self::LUNCH => 'Lunch',
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
