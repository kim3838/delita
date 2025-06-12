<?php

namespace App\Enums;

enum TimePeriodType: int implements BaseEnum
{
    case PAY_PERIOD = 0;
    case THIRTEENTH_MONTH = 1;

    public function label(): string
    {
        return match ($this) {
            self::PAY_PERIOD => 'Pay Period',
            self::THIRTEENTH_MONTH => '13th Month',
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
