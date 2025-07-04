<?php

namespace App\Enums;

enum PayPeriod: int implements BaseEnum
{
    case HOURLY = 0;
    case DAILY = 1;
    case SEMI_MONTHLY = 2;
    case MONTHLY = 3;

    public function label(): string
    {
        return match ($this) {
            self::HOURLY => "Hourly",
            self::DAILY => "Daily",
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
