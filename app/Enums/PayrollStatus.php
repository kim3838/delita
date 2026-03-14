<?php

namespace App\Enums;

enum PayrollStatus: int implements BaseEnum
{
    case DRAFT = 100;
    case WORKFLOW_IN_PROGRESS = 200;
    case COMPLETE = 300;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::WORKFLOW_IN_PROGRESS => 'WiP',
            self::COMPLETE => 'Complete',
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
