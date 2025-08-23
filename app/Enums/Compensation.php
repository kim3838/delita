<?php

namespace App\Enums;

enum Compensation: int implements BaseEnum
{
    case BASIC_SALARY = 0;
    case OVERTIME = 1;
    case REGULAR_ALLOWANCE = 2;
    case BENEFIT = 3;

    public function label(): string
    {
        return match ($this) {
            self::BASIC_SALARY => 'Basic Salary',
            self::OVERTIME => 'Overtime',
            self::BENEFIT => 'Benefit',
            self::REGULAR_ALLOWANCE => 'Regular Allowance',
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
