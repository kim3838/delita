<?php

namespace App\Enums;

enum AccountType: int implements BaseEnum
{
    case STANDARD = 0;
    case CORPORATE = 1;

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::CORPORATE => 'Corporate'
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
