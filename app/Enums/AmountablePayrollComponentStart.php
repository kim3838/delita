<?php

namespace App\Enums;

enum AmountablePayrollComponentStart: int implements BaseEnum
{
    case NOT_SPECIFIED = 0;
    case EMPLOYMENT_START_DATE = 100;
    case CUSTOM_DATE = 200;

    public function label(): string
    {
        return match ($this) {
            self::NOT_SPECIFIED => 'Not specified',
            self::EMPLOYMENT_START_DATE => 'Employment start date',
            self::CUSTOM_DATE => 'Custom date',
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
