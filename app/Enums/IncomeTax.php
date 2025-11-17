<?php

namespace App\Enums;

enum IncomeTax: int implements BaseEnum
{
    case COMPENSATION_TAX = 300;

    public function label(): string
    {
        return match ($this) {
            self::COMPENSATION_TAX => 'Compensation Tax',
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
