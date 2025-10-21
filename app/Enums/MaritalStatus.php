<?php

namespace App\Enums;

enum MaritalStatus: int implements BaseEnum
{
    case NOT_SPECIFIED = 0;
    case SINGLE = 100;
    case MARRIED = 200;
    case WIDOWED = 201;
    case DIVORCED = 202;
    case SEPARATED = 203;

    public function label(): string
    {
        return match ($this) {
            self::NOT_SPECIFIED => "Not Specified",
            self::SINGLE => "Single",
            self::MARRIED => "Married",
            self::WIDOWED => "Widowed",
            self::DIVORCED => "Divorced",
            self::SEPARATED => "Separated",
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
