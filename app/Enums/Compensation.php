<?php

namespace App\Enums;

enum Compensation: int implements BaseEnum
{
    case SALARY = 0;
    case OVERTIME = 1;
    case BENEFIT = 2;

    public function label(): string
    {
        return match ($this) {
            self::SALARY => 'Salary',
            self::OVERTIME => 'Overtime',
            self::BENEFIT => 'Benefit',
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
