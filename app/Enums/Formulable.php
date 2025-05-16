<?php

namespace App\Enums;

enum Formulable: int implements BaseEnum
{
    case EARNINGS = 0;
    case DEDUCTIONS = 1;
    case TAXABLE_INCOME = 2;
    case NON_TAXABLE_INCOME = 3;
    case INCOME_TAX = 4;
    case NET_INCOME = 5;

    public function label(): string
    {
        return match ($this) {
            self::EARNINGS => 'Earnings',
            self::DEDUCTIONS => 'Deductions',
            self::TAXABLE_INCOME => 'Taxable Income',
            self::NON_TAXABLE_INCOME => 'Non Taxable Income',
            self::INCOME_TAX => 'Income Tax',
            self::NET_INCOME => 'Net Income',
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
