<?php

namespace App\Enums;

enum Deduction: int implements BaseEnum
{
    case DEDUCTION = 200;
    case CONTRIBUTION = 210;

    public function label(): string
    {
        return match ($this) {
            self::DEDUCTION => 'Deduction',
            self::CONTRIBUTION => 'Contribution',
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
