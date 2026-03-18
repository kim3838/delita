<?php

namespace App\Enums;

enum ComponentValueLabelMap: string implements BaseEnum
{
    case REGULAR = 'regular';
    case REGULAR_PAY = 'regular_pay';
    case REST = 'rest_day_pay';
    case NIGHT = 'night_differential_pay';
    case MPF = 'mpf';

    public function label(): string
    {
        return match ($this) {
            self::REGULAR, self::REGULAR_PAY => 'Regular',
            self::REST => 'Rest day pay',
            self::NIGHT => 'Night diff. pay',
            self::MPF => 'Mandatory Provident Fund (MPF)',
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
