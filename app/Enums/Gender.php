<?php

namespace App\Enums;

enum Gender: int implements BaseEnum
{
    case NOT_SPECIFIED = 0;
    case MALE = 100;
    case FEMALE = 200;

    public function label(): string
    {
        return match ($this) {
            self::NOT_SPECIFIED => "Not Specified",
            self::MALE => "Male",
            self::FEMALE => "Female",
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
