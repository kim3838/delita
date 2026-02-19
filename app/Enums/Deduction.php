<?php

namespace App\Enums;

enum Deduction: int implements BaseEnum
{
    case STATUTORY_CONTRIBUTION = 200;
    case DEDUCTION = 210;

    public function label(): string
    {
        return match ($this) {
            self::STATUTORY_CONTRIBUTION => 'Statutory contribution',
            self::DEDUCTION => 'Deduction',
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
