<?php

namespace App\Enums;

enum CutOffType: int implements BaseEnum
{
    case WEEKDAY = 100;

    public function label(): string
    {
        return match ($this) {
            self::WEEKDAY => "Weekday",
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
