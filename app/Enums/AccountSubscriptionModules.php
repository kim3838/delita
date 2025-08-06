<?php

namespace App\Enums;

enum AccountSubscriptionModules: int implements BaseEnum
{
    case PAYROLL = 0;

    public function label(): string
    {
        return match ($this) {
            self::PAYROLL => 'Payroll',
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
