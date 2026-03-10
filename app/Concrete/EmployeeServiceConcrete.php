<?php

namespace App\Concrete;

use App\Blueprint\EmployeeServiceInterface;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Enums\EmploymentStatus;
use App\Enums\SalaryStatementType;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmployeeServiceConcrete implements EmployeeServiceInterface
{
    public Company $company;

    public function __construct(
        public Employee $employee
    ){
        $this->company = $employee->company;
    }

    /**
     * Identify if the employee has final pay before the given date (payroll start date)
     **/
    public function hasFinalPayBeforeDate(Employee $employee, Carbon $date): array
    {
        $finalPaySalaryStatement = app(SalaryStatementRepository::class)->list((object)[
            'employee_ids' => [$employee->id],
            'salary_statement_types' => [SalaryStatementType::FINAL_PAY->value],
            'payroll_is_after_start_date' => $date->toDateString(),
        ], ['payroll']);

        return [
            $finalPaySalaryStatement->isNotEmpty(),
            $finalPaySalaryStatement->first()
        ];
    }

    public function getPayrollAndEmploymentPayload(Payroll $payroll): array
    {
        $debugEnabled = false;

        $hasAtLeastOneEmployment = true;
        $hasEmploymentProfileWithinPayrollPeriod = true;

        if($this->employee->employmentProfiles->isEmpty()){
            $hasAtLeastOneEmployment = false;

            return [$payroll->isYearEnd, false, $hasAtLeastOneEmployment];
        }

        $payrollStartDate = $payroll->start_date;
        $payrollEndDate = $payroll->end_date;
        $nextPayrollStartDate = $payroll->end_date->copy()->addDay();

        $currentOrUpcomingEmploymentProfilesQueryBuilder = $this->employee->employmentProfiles()
            ->getQuery()
            ->whereIn('status', [EmploymentStatus::ACTIVE->value])
            ->where(function ($query) use ($nextPayrollStartDate){
                $query->where(function ($query) use ($nextPayrollStartDate){
                    $query->whereNull('end_date')
                        ->where('start_date', '<=', $nextPayrollStartDate->toDateString());
                })->orWhere(function ($query) use ($nextPayrollStartDate){
                    $query->whereNull('end_date')
                        ->where('start_date', '>=', $nextPayrollStartDate->toDateString());
                })->orWhere(function ($query) use ($nextPayrollStartDate){
                    $query->whereNotNull('end_date')
                        ->where('end_date', '>=', $nextPayrollStartDate->toDateString());
                });
            });

        $currentAndUpcomingEmploymentProfiles = $currentOrUpcomingEmploymentProfilesQueryBuilder->get();

        $employmentProfileWithinPayrollPeriodQueryBuilder = $this->employee->employmentProfiles()
            ->getQuery()
            ->whereIn('status', [EmploymentStatus::ACTIVE->value])
            ->where(function ($query) use ($payrollStartDate, $payrollEndDate){
                $query->where(function ($query) use ($payrollEndDate){
                    $query->whereNull('end_date')
                        ->where('start_date', '<=', $payrollEndDate->toDateString());
                })->orWhere(function ($query) use ($payrollStartDate, $payrollEndDate){
                    $query->whereNotNull('end_date')
                        ->where('start_date', '<=', $payrollEndDate->toDateString())
                        ->where('end_date', '>=', $payrollStartDate->toDateString());
                });
            });

        $employmentProfileWithinPayrollPeriod = $employmentProfileWithinPayrollPeriodQueryBuilder->get();

        $hasEmploymentProfileWithinPayrollPeriod = !$employmentProfileWithinPayrollPeriod->isEmpty();

        if($debugEnabled){

            _debug([
                'Employee' => $this->employee->full_name,
                'Next payroll start date' => $nextPayrollStartDate->toDateString(),
                'Yar end' => $payroll->isYearEnd,
                'Payroll start date' => $payrollStartDate->toDateString(),
                'Payroll end date' => $payrollEndDate->toDateString(),
                'Has current ending and no upcoming employment' => $currentAndUpcomingEmploymentProfiles->isEmpty(),
                'Has employment profile within period' => !$employmentProfileWithinPayrollPeriod->isEmpty(),
                'Has to annualize early' => !$payroll->isYearEnd && $currentAndUpcomingEmploymentProfiles->isEmpty()
            ]);
        }

        return [
            $payroll->isYearEnd,
            $currentAndUpcomingEmploymentProfiles->isEmpty(),
            $hasAtLeastOneEmployment,
            $hasEmploymentProfileWithinPayrollPeriod
        ];
    }

    public function getEmployeeShiftFromEmployeeShiftCollection(Collection $employeeShifts, Carbon $date): ?EmployeeShift
    {
        $employeeShiftByDate = $employeeShifts->filter(function ($employeeShift) use ($date){

            $dateIsWithinRange = $employeeShift?->stated_shift_end_date &&
                $employeeShift?->start_date?->lte($date) && $employeeShift?->end_date?->gte($date);

            $dateIsEqualOrAfterNonEndStatedShift = !$employeeShift?->stated_shift_end_date &&
                $date?->gte($employeeShift->start_date);

            return $dateIsWithinRange || $dateIsEqualOrAfterNonEndStatedShift;
        });

        $employeeShiftPivot = $employeeShiftByDate->first();

        return $employeeShiftPivot instanceof EmployeeShift ? $employeeShiftPivot : null;
    }
}
