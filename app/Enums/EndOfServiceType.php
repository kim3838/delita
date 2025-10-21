<?php

namespace App\Enums;

enum EndOfServiceType: int implements BaseEnum
{
    case NOT_SPECIFIED = 0;
    case END_OF_CONTRACT = 100;
    case RESIGNED = 200;
    case TERMINATED = 300;
    case RETIRED = 400;
    case DEATH = 500;
    case MEDICAL_SEPARATION = 600;
    case DISABILITY = 700;

    public function label(): string
    {
        return match ($this) {
            self::END_OF_CONTRACT => 'End of contract',
            self::RESIGNED => 'Resigned',
            self::TERMINATED => 'Terminated',
            self::RETIRED => 'Retired',
            self::DEATH => 'Death',
            self::MEDICAL_SEPARATION => 'Medical separation',
            self::DISABILITY => 'Disability',
            self::NOT_SPECIFIED => 'Not specified',
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
