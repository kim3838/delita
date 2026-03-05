<?php

namespace App\Enums;

enum PayFrequency: int implements BaseEnum
{
    case WEEKLY = 200;
    case SEMIMONTHLY = 300;
    case MONTHLY = 400;

    public function label(): string
    {
        return match ($this) {
            self::WEEKLY => "Weekly",
            self::SEMIMONTHLY => "Semimonthly",
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
