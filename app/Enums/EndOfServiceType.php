<?php

namespace App\Enums;

enum EndOfServiceType: int implements BaseEnum
{
    case END_OF_CONTRACT = 0;
    case RESIGNED = 1;
    case TERMINATED = 2;
    case RETIRED = 3;
    case DEATH = 4;
    case MEDICAL_SEPARATION = 5;
    case DISABILITY = 6;
    case NOT_SPECIFIED = 7;

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
