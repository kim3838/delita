<?php

namespace App\Enums;

enum FormulableComponentSubType: string implements BaseEnum
{
    case BASIC_PAY = '100.100.1';
    case REGULAR_ALLOWANCE = '100.101.1';
    case MEAL_ALLOWANCE = '100.101.2';
    case COFFEE_ALLOWANCE = '100.101.3';
    case TRANSPORTATION_ALLOWANCE = '100.101.4';
    case OVERTIME = '100.110.1';
    case STATUTORY_BENEFIT_13TH_MONTH = '100.120.1';
    case STATUTORY_BENEFIT_13TH_MONTH_POSITIVE_ADJUSTMENT = '100.120.2';
    case NONSTATUTORY_BENEFIT_BONUS = '100.130.1';
    case LEAVE_PAY = '100.200.1';
    case HOLIDAY_PAY = '100.300.1';
    case TAX_REFUND = '100.1000.1';

    case PH_SSS = '200.200.2';
    case PH_PHILHEALTH = '200.200.3';
    case PH_PAG_IBIG = '200.200.4';
    case DEDUCTION = '200.210.1';
    case TAX_DEFICIT = '200.1000.1';
    case STATUTORY_BENEFIT_13TH_MONTH_NEGATIVE_ADJUSTMENT = '200.2000.2';

    case PH_WITHHOLDING_TAX_COMPENSATION = '500.300.2';

    public function label(): string
    {
        return match ($this) {
            self::BASIC_PAY => 'Basic pay',
            self::REGULAR_ALLOWANCE => 'Regular allowance',
            self::MEAL_ALLOWANCE => 'Meal allowance',
            self::COFFEE_ALLOWANCE => 'Coffee allowance',
            self::TRANSPORTATION_ALLOWANCE => 'Transportation allowance',
            self::OVERTIME => 'Overtime',
            self::STATUTORY_BENEFIT_13TH_MONTH => '13th month pay',
            self::STATUTORY_BENEFIT_13TH_MONTH_POSITIVE_ADJUSTMENT => '13th month differential',
            self::NONSTATUTORY_BENEFIT_BONUS => 'Bonus',
            self::LEAVE_PAY => 'Leave pay',
            self::HOLIDAY_PAY => 'Holiday pay',
            self::TAX_REFUND => 'Tax refund',

            self::PH_SSS => 'SSS contribution',
            self::PH_PHILHEALTH => 'Philhealth (PHIC)',
            self::PH_PAG_IBIG => 'Pag-IBIG (HDMF)',
            self::DEDUCTION => 'Deduction',
            self::TAX_DEFICIT => 'Tax deficit',
            self::STATUTORY_BENEFIT_13TH_MONTH_NEGATIVE_ADJUSTMENT => '13th month excess deduction',

            self::PH_WITHHOLDING_TAX_COMPENSATION => 'Compensation tax (WTC)',
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
