<?php

namespace App\Enums;

enum AccountSubscriptionModules: int implements BaseEnum
{
    case EMPLOYEE_PORTAL = 50;
    case HR_PAYROLL = 100;
    case INVENTORY = 200;
    case FINANCE_ACCOUNTING = 300;

    public function label(): string
    {
        return match ($this) {
            self::EMPLOYEE_PORTAL => 'Employee Portal',
            self::HR_PAYROLL => 'HR & Payroll',
            self::INVENTORY => 'Inventory',
            self::FINANCE_ACCOUNTING => 'Finance & Accounting',
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
