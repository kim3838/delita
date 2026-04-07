<?php

namespace App\Enums;

enum Sort: string implements BaseEnum
{
    case ASC = 'ASC';
    case DESC = 'DESC';

    public function label(): string
    {
        return match ($this) {
            self::ASC => 'Ascending',
            self::DESC => 'Descending',
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
