<?php

namespace App\Enums;

enum DepartmentEmployeeAssignmentType: int implements BaseEnum
{
    case DEFAULT = 0;
    case HEAD = 100;

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
            self::HEAD => 'Head',
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
