<?php

namespace App\Enums;

enum RequestableType: string implements BaseEnum
{
    case OVERTIME = 'overtime_request';
    case ATTENDANCE_ADJUSTMENT = 'attendance_adjustment_request';
    case LEAVE = 'leave_request';

    public function label(): string
    {
        return match ($this) {
            self::OVERTIME => 'Overtime request',
            self::ATTENDANCE_ADJUSTMENT => 'Attendance adjustment request',
            self::LEAVE => 'Leave request',
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
