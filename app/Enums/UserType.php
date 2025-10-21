<?php

namespace App\Enums;

enum UserType: int implements BaseEnum
{
    case DEFAULT = 0;
    case ADMIN = 100;
    case SUPER_ADMIN = 200;

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin'
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
