<?php

namespace App\Enums;

enum Formulable: int implements BaseEnum
{
    case EARNINGS = 100;
    case DEDUCTIONS = 200;
    case TAXABLE_INCOME = 300;
    case NONTAXABLE_INCOME = 400;
    case INCOME_TAX = 500;
    case NET_INCOME = 600;

    public function label(): string
    {
        return match ($this) {
            self::EARNINGS => 'Earnings',
            self::DEDUCTIONS => 'Deductions',
            self::TAXABLE_INCOME => 'Taxable Income',
            self::NONTAXABLE_INCOME => 'Nontaxable Income',
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
