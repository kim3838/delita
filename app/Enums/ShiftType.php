<?php

namespace App\Enums;

enum ShiftType: int implements BaseEnum
{
    case REGULAR = 100;
    case GRAVEYARD = 200;

    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular',
            self::GRAVEYARD => 'Graveyard',
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
