<?php

namespace App\Enums;

enum LeaveCarryOverType: int implements BaseEnum
{
    case ALL = 100;
    case LIMIT = 200;
    case PERCENTAGE = 300;

    public function label(): string
    {
        return match ($this) {
            self::ALL => 'All',
            self::LIMIT => 'Limit',
            self::PERCENTAGE => 'Percentage',
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
