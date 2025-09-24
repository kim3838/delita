<?php

namespace App\Enums;

enum WorkHourType: int implements BaseEnum
{
    case REGULAR = 0;
    case NIGHT = 1;

    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular',
            self::NIGHT => 'Night',
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
