<?php

namespace App\Enums;

enum IncomeTax: int implements BaseEnum
{
    case WITHHOLDING_TAX = 0;

    public function label(): string
    {
        return match ($this) {
            self::WITHHOLDING_TAX => 'Withholding Tax',
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
