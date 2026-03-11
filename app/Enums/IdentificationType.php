<?php

namespace App\Enums;

enum IdentificationType: string implements BaseEnum
{
    case PH_SSS = 'PH.2';
    case PH_PHILHEALTH = 'PH.3';
    case PH_PAG_IBIG = 'PH.4';
    case PH_TIN = 'PH.5';

    public function label(): string
    {
        return match ($this) {
            self::PH_SSS => 'SSS (Social Security System)',
            self::PH_PHILHEALTH => 'Philhealth (PHIC)',
            self::PH_PAG_IBIG => 'Pag-IBIG (HDMF)',
            self::PH_TIN => 'TIN',
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
