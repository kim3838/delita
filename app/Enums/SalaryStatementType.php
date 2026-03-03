<?php

namespace App\Enums;

enum SalaryStatementType: int implements BaseEnum
{
    case DEFAULT = 0;
    case FINAL_PAY = 50;

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
            self::FINAL_PAY => 'Final pay',
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
