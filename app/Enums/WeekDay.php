<?php

namespace App\Enums;

use Carbon\CarbonInterface;

enum WeekDay: int implements BaseEnum
{
    case SUNDAY = CarbonInterface::SUNDAY;
    case MONDAY = CarbonInterface::MONDAY;
    case TUESDAY = CarbonInterface::TUESDAY;
    case WEDNESDAY = CarbonInterface::WEDNESDAY;
    case THURSDAY = CarbonInterface::THURSDAY;
    case FRIDAY = CarbonInterface::FRIDAY;
    case SATURDAY = CarbonInterface::SATURDAY;

    public function label(): string
    {
        return match ($this) {
            self::SUNDAY => 'Sunday',
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
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
