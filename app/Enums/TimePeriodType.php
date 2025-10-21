<?php

namespace App\Enums;

enum TimePeriodType: int implements BaseEnum
{
    case PAY_FREQUENCY = 100;
    case THIRTEENTH_MONTH = 200;
    case NIGHT_DIFFERENTIAL_HOURS = 300;

    public function label(): string
    {
        return match ($this) {
            self::PAY_FREQUENCY => 'Pay Period',
            self::THIRTEENTH_MONTH => '13th Month',
            self::NIGHT_DIFFERENTIAL_HOURS => 'Night Differential Hours',
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
