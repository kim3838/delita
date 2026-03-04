<?php

namespace App\Enums;

enum FormulableComponentSubType: string implements BaseEnum
{
    case STATUTORY_BENEFIT_13TH_MONTH = '100.120.1';

    public function label(): string
    {
        return match ($this) {
            self::STATUTORY_BENEFIT_13TH_MONTH => 'Statutory Benefit 13th Month',
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
