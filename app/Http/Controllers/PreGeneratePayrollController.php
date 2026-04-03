<?php

namespace App\Http\Controllers;

use App\Blueprint\EmployeeServiceInterface;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\EmploymentStatus;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\PreGeneratePayroll\PreGeneratePayrollRequest;
use App\Transformers\EmploymentProfile\ListTransformer;

class PreGeneratePayrollController extends Controller
{
    /**
     * Pre-generate payroll
     *
     * Filter out employees that already have final pay, employment profile not within payroll period, and no active employment profile
     * Build warning info for employees that final pay is about to be generated
     *
     * @throws UnexpectedException
     */
    public function store(PreGeneratePayrollRequest $request)
    {
        if($request->expectsJson()){

            $debugEnabled = false;

            $validated = $request->validated();
            $companyId = $validated['company_id'];
            $year = $validated['year'];
            $month = $validated['month'];
            $payFrequencyValue = $validated['pay_frequency'];
            $frequencySequence = $validated['frequency_sequence'];

            $payrollHydration = app(PayrollRepository::class)->hydrateItem([
                'year' => $year,
                'month' => $month,
                'pay_frequency' => $payFrequencyValue,
                'frequency_sequence' => $frequencySequence,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            $payFrequency = app(PayFrequencyRepository::class)->model()::query()
                ->where('company_id', $companyId)
                ->where('type', $payFrequencyValue)
                ->first();

            if(empty($payFrequency)){

                return ResponseJson::successfulResponse([
                    'employment_profiles' => []
                ]);
            }

            $employeeFilters = (object)[
                'company_id' => $companyId,
                'employee_ids' => [],
                'pay_frequency_ids' => [$payFrequency->id],
            ];

            $employees = app(EmployeeRepository::class)->queryBuilderCursor($employeeFilters);

            $generatePayrollEmployeeIds = [];
            $employmentProfilesFinalPay = collect();

            foreach($employees as $employee){

                $employee = app(EmployeeRepository::class)->hydrateItem($employee);

                $employeeService = app(EmployeeServiceInterface::class, [$employee]);

                list(
                    $hasFinalPayBeforeDate, $finalPaySalaryStatement
                ) = $employeeService->hasFinalPayBeforeDate($employee, $payrollHydration->start_date);

                if($debugEnabled){

                    _debug([
                        'Pre-generate payroll check' => $employee->number . ' ' .$employee->full_name_attribute,
                        'Date' => $payrollHydration->start_date->toDateString(),
                        'Has final pay before payroll start date' => $hasFinalPayBeforeDate,
                    ]);
                }

                /**
                 * Skip payroll generation if the employee already has final pay
                 * And that final pay is
                 **/
                if($hasFinalPayBeforeDate) continue;

                /**
                 * Build warning info for employees that final pay is about to be generated
                 **/
                list(
                    $isYearEnd, $currentEndingOrNoUpcomingEmployment, $hasAtLeastOneEmployment, $hasEmploymentProfileWithinPayrollPeriod
                ) = $employeeService->getPayrollAndEmploymentPayload($payrollHydration);

                if($debugEnabled){

                    _debug([
                        'Has current ending and no upcoming employment' => $currentEndingOrNoUpcomingEmployment,
                        'Has at least one employment profile' => $hasAtLeastOneEmployment,
                        'Has employment profile within payroll period' => $hasEmploymentProfileWithinPayrollPeriod,
                        'Generate payroll?' => $hasAtLeastOneEmployment && $hasEmploymentProfileWithinPayrollPeriod
                    ]);
                }

                /**
                 * Skip payroll generation if the employee does not have any employment profile
                 **/
                if(!$hasAtLeastOneEmployment) continue;

                /**
                 * Skip payroll generation if the employee does not have any employment profile within payroll period
                 **/
                if(!$hasEmploymentProfileWithinPayrollPeriod) continue;

                /**
                 * Include employee to generate payroll
                 **/
                $generatePayrollEmployeeIds[] = $employee->id;

                if($currentEndingOrNoUpcomingEmployment){

                    $payrollEndDate = $payrollHydration->end_date->copy();

                    $proximityEmploymentProfilesQueryBuilder = $employee->employmentProfiles()
                        ->getQuery()
                        ->whereIn('status', [EmploymentStatus::ACTIVE->value])
                        ->where(function ($query) use ($payrollEndDate){
                            $query->where(function ($query) use ($payrollEndDate){
                                $query->whereNotNull('end_date')
                                    ->where('end_date', '<=', $payrollEndDate->toDateString());
                            });
                        })
                        ->orderBy('start_date', 'asc');

                    $proximityEmploymentProfile = $proximityEmploymentProfilesQueryBuilder->get()->last();

                    if(empty($proximityEmploymentProfile)) continue;

                    $employmentProfilesFinalPay->push($proximityEmploymentProfile);
                }
            }

            if($debugEnabled){

                _debug([
                    'Pre-generate employee ids' => $generatePayrollEmployeeIds,
                ]);
            }

            return ResponseJson::successfulResponse([
                'generate_payroll_employee_ids' => $generatePayrollEmployeeIds,
                'employment_profiles_final_pay' => Fractal::collection($employmentProfilesFinalPay, ListTransformer::class)['data']
            ]);
        }

        abort(404);
    }
}
