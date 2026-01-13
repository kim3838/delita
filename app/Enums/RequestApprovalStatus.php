<?php

namespace App\Enums;

enum RequestApprovalStatus: int implements BaseEnum
{
    case PENDING = 0;
    case DECLINED = 100;
    case APPROVED = 200;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::DECLINED => 'Declined',
            self::APPROVED => 'Approved',
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
