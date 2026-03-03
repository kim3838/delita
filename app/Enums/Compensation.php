<?php

namespace App\Enums;

enum Compensation: int implements BaseEnum
{
    case BASIC_PAY = 100;
    case REGULAR_ALLOWANCE = 101;
    case OVERTIME = 110;
    case STATUTORY_BENEFIT = 120;
    case BENEFIT = 130;
    case LEAVE_PAY = 200;
    case HOLIDAY_PAY = 300;
    case TAX_REFUND = 1000;

    public function label(): string
    {
        return match ($this) {
            self::BASIC_PAY => 'Basic pay',
            self::REGULAR_ALLOWANCE => 'Regular allowance',
            self::OVERTIME => 'Overtime',
            self::STATUTORY_BENEFIT => 'Statutory benefit',
            self::BENEFIT => 'Benefit',
            self::LEAVE_PAY => 'Leave pay',
            self::HOLIDAY_PAY => 'Holiday pay',
            self::TAX_REFUND => 'Tax refund',
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
