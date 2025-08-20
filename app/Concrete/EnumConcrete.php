<?php

namespace App\Concrete;

use App\Blueprint\EnumInterface;
use App\Enums\AccountPlan;
use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\Gender;
use App\Enums\IncomeTax;
use App\Enums\MaritalStatus;
use App\Enums\PayPeriod;
use App\Enums\PayType;
use App\Enums\UserType;

class EnumConcrete implements EnumInterface
{
    public function selection($enum): string
    {
        return match ($enum) {
            'account_plan' => AccountPlan::class,
            'company_user_assignment_type' => CompanyUserAssignmentType::class,
            'compensation' => Compensation::class,
            'deduction' => Deduction::class,
            'income_tax' => IncomeTax::class,
            'gender' => Gender::class,
            'marital_status' => MaritalStatus::class,
            'user_type' => UserType::class,
            'formulable_type' => Formulable::class,
            'pay_period' => PayPeriod::class,
            'pay_type' => PayType::class,
            default => throw new \InvalidArgumentException('Invalid enum type'),
        };
    }
}
