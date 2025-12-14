<?php

namespace App\Enums;

enum LeaveBalanceAdjustmentType: int implements BaseEnum
{
    case ADD = 100;
    case DEDUCT = 200;

    public function label(): string
    {
        return match ($this) {
            self::ADD => 'Add',
            self::DEDUCT => 'Deduct',
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
