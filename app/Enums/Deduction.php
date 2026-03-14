<?php

namespace App\Enums;

enum Deduction: int implements BaseEnum
{
    case STATUTORY_CONTRIBUTION = 200;
    case DEDUCTION = 210;
    case MANUAL_DEDUCTION = 400;
    case TAX_ADJUSTMENT = 1000;
    case THIRTEENTH_MONTH_ADJUSTMENT = 2000;

    public function label(): string
    {
        return match ($this) {
            self::STATUTORY_CONTRIBUTION => 'Statutory contribution',
            self::DEDUCTION => 'Deduction',
            self::MANUAL_DEDUCTION => 'Manual deduction',
            self::TAX_ADJUSTMENT => 'Tax adjustment',
            self::THIRTEENTH_MONTH_ADJUSTMENT => '13th month adjustment',
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
