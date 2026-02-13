<?php

namespace App\Concrete;

use App\Enums\Compensation as CompensationEnum;
use App\Enums\Formulable;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Collection;

class SalaryStatementModuleServiceConcrete
{
    public array $moduleKeyMap = [];

    public Employee $employee;

    public Collection $salaryStatementModules;

    public function __construct(
        protected Company $company,
    ){
        $this->salaryStatementModules = $this->company->salaryStatementModules;
    }

    public function setEmployee(Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function companyPerDayAbleEarningsMorphFilterSlugs(): array
    {
        /**
         * Company per day-able compensations: (Basic pay, Allowance, Overtime)
         **/
        $companyPerDayAbleEarnings = $this->company->compensations->where('assignable', true)
            ->where('formulable_type', Formulable::EARNINGS->value)
            ->whereIn('type', [CompensationEnum::BASIC_PAY, CompensationEnum::REGULAR_ALLOWANCE, CompensationEnum::OVERTIME]);

        $companyPerDayAbleEarningsMorphFilterSlugs = $companyPerDayAbleEarnings
            ->map(fn($companyPerDayEarning) => $companyPerDayEarning->id . '.compensation')
            ->values()
            ->toArray();

        return $companyPerDayAbleEarningsMorphFilterSlugs;
    }

    public function companyPerDayableGlobalCompensations(): Collection
    {
        /**
         * Company global compensations: (Leave pay, Holiday pay)
         **/
        return $this->company->compensations
            ->where('assignable', false)
            ->where('formulable_type', Formulable::EARNINGS->value)
            ->whereIn('type', [CompensationEnum::HOLIDAY_PAY, CompensationEnum::LEAVE_PAY])
            ->sortBy('order');
    }
}
