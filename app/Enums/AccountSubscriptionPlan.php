<?php

namespace App\Enums;

enum AccountSubscriptionPlan: int implements BaseEnum
{
    case STANDARD = 100;
    case BUSINESS = 500;

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
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
