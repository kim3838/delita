<?php

namespace App\Enums;

enum Compensation: int implements BaseEnum
{
    case BASIC_PAY = 100;
    case REGULAR_ALLOWANCE = 101;
    case OVERTIME = 110;
    case BENEFIT = 120;
    case LEAVE_PAY = 200;
    case HOLIDAY_PAY = 300;

    public function label(): string
    {
        return match ($this) {
            self::BASIC_PAY => 'Basic pay',
            self::OVERTIME => 'Overtime',
            self::BENEFIT => 'Benefit',
            self::REGULAR_ALLOWANCE => 'Regular allowance',
            self::LEAVE_PAY => 'Leave pay',
            self::HOLIDAY_PAY => 'Holiday pay',
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
