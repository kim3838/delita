<?php

namespace App\Enums;

enum HolidayType: int implements BaseEnum
{
    case SPECIAL = 100;
    case LEGAL = 200;
    case DOUBLE = 300;

    public function label(): string
    {
        return match ($this) {
            self::SPECIAL => 'Special',
            self::LEGAL => 'Legal',
            self::DOUBLE => 'Double',
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
