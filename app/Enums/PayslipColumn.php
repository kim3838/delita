<?php

namespace App\Enums;

enum PayslipColumn: int implements BaseEnum
{
    case EARNINGS = 100;
    case DEDUCTIONS = 101;
    case SUMMARY = 200;

    public function label(): string
    {
        return match ($this) {
            self::EARNINGS => 'Earnings',
            self::DEDUCTIONS => 'Deductions',
            self::SUMMARY => 'Summary',
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
