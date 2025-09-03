<?php

namespace App\Enums;

enum EmploymentType: int implements BaseEnum
{
    case OJT = 0;
    case INTERN = 1;
    case PROBATIONARY = 2;
    case FULL_TIME = 3;
    case PART_TIME = 4;
    case CONTRACT = 5;
    case NOT_SPECIFIED = 6;

    public function label(): string
    {
        return match ($this) {
            self::OJT => 'OJT',
            self::INTERN => 'Intern',
            self::PROBATIONARY => 'Probationary',
            self::FULL_TIME => 'Full time',
            self::PART_TIME => 'Part time',
            self::CONTRACT => 'Contract',
            self::NOT_SPECIFIED => 'Not specified',
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
