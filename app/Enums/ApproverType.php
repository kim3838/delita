<?php

namespace App\Enums;

enum ApproverType: int implements BaseEnum
{
    case SELECTED = 0;
    case DEPARTMENT_HEAD = 100;
    case MANAGER = 200;

    public function label(): string
    {
        return match ($this) {
            self::SELECTED => 'Selected',
            self::DEPARTMENT_HEAD => 'Department head',
            self::MANAGER => 'Manager',
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

    public static function valuesToArray(): array
    {
        return array_map(function($enum){return $enum['value'];},self::all());
    }
}
