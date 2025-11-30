<?php

namespace App\Enums;

enum LeaveType: int implements BaseEnum
{
    case VACATION = 100;
    case SICK = 200;
    case EMERGENCY = 300;

    public function label(): string
    {
        return match ($this) {
            self::VACATION => 'Vacation',
            self::SICK => 'Sick',
            self::EMERGENCY => 'Emergency',
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
