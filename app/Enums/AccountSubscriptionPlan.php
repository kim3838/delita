<?php

namespace App\Enums;

enum AccountSubscriptionPlan: int implements BaseEnum
{
    case STANDARD = 100;
    case STANDARD_PLUS = 101;
    case STANDARD_PLUS_2 = 102;
    case CORPORATE = 200;
    case BUSINESS = 500;

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::STANDARD_PLUS => 'Standard +1',
            self::STANDARD_PLUS_2 => 'Standard +2',
            self::CORPORATE => 'Corporate',
            self::BUSINESS => 'Business',
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
