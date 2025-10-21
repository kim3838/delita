<?php

namespace App\Enums;

enum PayType: int implements BaseEnum
{
    case BY_ATTENDANCE = 100;
    case FIXED = 200;

    public function label(): string
    {
        return match ($this) {
            self::BY_ATTENDANCE => "By attendance",
            self::FIXED => "Fixed",
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

