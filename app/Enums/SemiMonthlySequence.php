<?php

namespace App\Enums;

enum SemiMonthlySequence: int implements BaseEnum
{
    case FIRST_HALF = 100;
    case SECOND_HALF = 200;

    public function label(): string
    {
        return match ($this) {
            self::FIRST_HALF => '1st half',
            self::SECOND_HALF => '2nd half',
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
