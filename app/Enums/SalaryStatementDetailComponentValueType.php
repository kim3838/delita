<?php

namespace App\Enums;

enum SalaryStatementDetailComponentValueType: int implements BaseEnum
{
    case PH_BASIC_PAY = 100;
    case PH_REGULAR_ALLOWANCE = 200;
    case PH_OVERTIME = 300;
    case PH_LEAVE = 400;
    case PH_HOLIDAY = 500;
    case PH_SSS = 600;
    case PH_PHILHEALTH = 700;
    case PH_PAG_IBIG = 800;
    case PH_WITHHOLDING_TAX = 900;
    case NET = 5000;

    public function label(): string
    {
        return match ($this) {
            self::PH_BASIC_PAY => 'Basic pay',
            self::PH_REGULAR_ALLOWANCE => 'Regular Allowance',
            self::PH_OVERTIME => 'Overtime',
            self::PH_LEAVE => 'Leave',
            self::PH_HOLIDAY => 'Holiday',
            self::PH_SSS => 'Ph SSS',
            self::PH_PHILHEALTH => 'Ph PhilHealth',
            self::PH_PAG_IBIG => 'Ph Pag-Ibig',
            self::PH_WITHHOLDING_TAX => 'Ph Withholding Tax',
            self::NET => 'Net',
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
