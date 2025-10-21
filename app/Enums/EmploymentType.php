<?php

namespace App\Enums;

enum EmploymentType: int implements BaseEnum
{
    case NOT_SPECIFIED = 0;
    case OJT = 100;
    case INTERN = 101;
    case PROBATIONARY = 200;
    case FULL_TIME = 201;
    case PART_TIME = 300;
    case CONTRACT = 400;

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
