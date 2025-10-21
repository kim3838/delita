<?php

namespace App\Enums;

enum GroupType: int implements BaseEnum
{
    case EMPLOYEE = 100;

    public function label(): string
    {
        return match ($this) {
            self::EMPLOYEE => 'Employee',
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
