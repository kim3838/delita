<?php

namespace App\Enums;

enum PayFrequency: int implements BaseEnum
{
    case SEMI_MONTHLY = 0;
    case MONTHLY = 1;

    public function label(): string
    {
        return match ($this) {
            self::SEMI_MONTHLY => "Semimonthly",
            self::MONTHLY => "Monthly",
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
