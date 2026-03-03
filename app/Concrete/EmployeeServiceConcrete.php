<?php

namespace App\Concrete;

use App\Blueprint\EmployeeServiceInterface;
use App\Enums\EmploymentStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;

class EmployeeServiceConcrete implements EmployeeServiceInterface
{
    public Company $company;

    public function __construct(
        public Employee $employee
    ){
        $this->company = $employee->company;
    }

    public function hasToAnnualizePayroll(Payroll $payroll): bool
    {
        $debugEnabled = false;

        $nextPayrollStartDate = $payroll->end_date->copy()->addDay();

        $currentOrUpcomingEmploymentProfilesQueryBuilder = $this->employee->employmentProfiles()
            ->getQuery()
            ->whereIn('status', [EmploymentStatus::ACTIVE->value])
            ->where(function ($query) use ($nextPayrollStartDate){
                $query->where('start_date', '>=', $nextPayrollStartDate->toDateString())
                    ->orWhere(function ($query) use ($nextPayrollStartDate){
                        $query->whereNotNull('end_date')
                            ->where('end_date', '>=', $nextPayrollStartDate->toDateString());
                    });
            });

        $currentOrUpcomingEmploymentProfiles = $currentOrUpcomingEmploymentProfilesQueryBuilder->get();

        if($debugEnabled){

            _debug([
                'Not yet year end' => !$payroll->isYearEnd,
                'Has no current and upcoming employment' => $currentOrUpcomingEmploymentProfiles->isEmpty(),
            ]);
        }

        return !$payroll->isYearEnd && $currentOrUpcomingEmploymentProfiles->isEmpty();
    }
}
