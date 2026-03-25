<?php

namespace App\Transformers\SalaryStatement;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Enums\DepartmentEmployeeAssignmentType;
use App\Facades\Fractal;
use App\Facades\MoneyFormat;
use App\Models\SalaryStatement;
use App\Transformers\Payroll\BasicTransformer as PayrollBasicTransformer;
use App\Transformers\SalaryStatementAttendance\DetailedListTransformer as SalaryStatementAttendanceDetailedListTransformer;
use App\Transformers\SalaryStatementDetail\ListTransformer as SalaryStatementDetailListTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(SalaryStatement $salaryStatement): array
    {
        $companyCurrencyCode = $salaryStatement->payroll->company->currency;

        $payroll = Fractal::item($salaryStatement->payroll, PayrollBasicTransformer::class);

        $employeeRepositoryFilters = (object)[
            'employee_ids' => [$salaryStatement->employee_id],
        ];

        $employee = App::make(EmployeeRepository::class)->list($employeeRepositoryFilters, ['department', 'designation'])->first();

        $salaryStatementAttendanceRepositoryFilters = (object)[
            'salary_statement_ids' => [$salaryStatement->id],
        ];

        $salaryStatementAttendances = App::make(SalaryStatementAttendanceRepository::class)->list($salaryStatementAttendanceRepositoryFilters);

        $statementAttendances = Fractal::collection($salaryStatementAttendances, SalaryStatementAttendanceDetailedListTransformer::class)['data'];

        $salaryStatementDetailRepositoryFilters = (object)[
            'payroll_ids' => [$salaryStatement->payroll->id],
            'company_ids' => [$salaryStatement->payroll->company_id],
            'employee_ids' => [$salaryStatement->employee_id],
        ];

        $statementDetails = Fractal::collection(
            app(SalaryStatementDetailRepository::class)->list($salaryStatementDetailRepositoryFilters, ['salary_statement']),
            SalaryStatementDetailListTransformer::class
        )['data'];

        $taxable = MoneyFormat::toLocale($salaryStatement->taxable, $companyCurrencyCode);
        $nontaxable = MoneyFormat::toLocale($salaryStatement->nontaxable, $companyCurrencyCode);
        $contribution = MoneyFormat::toLocale($salaryStatement->contribution, $companyCurrencyCode);
        $withholding_tax = MoneyFormat::toLocale($salaryStatement->withholding_tax, $companyCurrencyCode);
        $deduction = MoneyFormat::toLocale($salaryStatement->deduction, $companyCurrencyCode);
        $net = MoneyFormat::toLocale($salaryStatement->net, $companyCurrencyCode);

        return [
            'row_number' => $salaryStatement->row_number,
            'id' => $salaryStatement->id,
            'ulid' => $salaryStatement->ulid,
            'payroll_id' => $salaryStatement->payroll_id,
            'employee_id' => $salaryStatement->employee_id,
            'type' => $salaryStatement->type?->toArray(),
            'is_paid' => $salaryStatement->is_paid,

            'payroll' => $payroll,

            'employee_number' => $employee->number,
            'employee_full_name' => $employee->full_name,
            'employee_department' => $employee->department_employee_id
                ? [
                    'name' => $employee->department_name,
                    'assignment_type' => DepartmentEmployeeAssignmentType::tryFrom($employee->department_assignment_type)?->toArray()
                ] : null,
            'employee_designation' => $employee->designation_id
                ? ['name' => $employee->designation_name]
                : null,

            'total_days' => $salaryStatement->total_days,
            'total_day_offs' => $salaryStatement->total_day_offs,
            'total_working_days' => $salaryStatement->total_working_days,
            'total_regular_work_days' => $salaryStatement->total_regular_work_days,
            'total_working_rest_days' => $salaryStatement->total_working_rest_days,
            'total_special_holidays' => $salaryStatement->total_special_holidays,
            'total_legal_holidays' => $salaryStatement->total_legal_holidays,
            'total_double_holidays' => $salaryStatement->total_double_holidays,
            'total_legal_and_double_holidays' => $salaryStatement->total_legal_holidays + $salaryStatement->total_double_holidays,
            'total_present' => $salaryStatement->total_full_present + $salaryStatement->total_present_with_irregularity,
            'total_full_present' => $salaryStatement->total_full_present,
            'total_present_with_irregularity' => $salaryStatement->total_present_with_irregularity,
            'total_leaves' => $salaryStatement->total_leave_without_pay + $salaryStatement->total_leave_with_pay,
            'total_leave_without_pay' => $salaryStatement->total_leave_without_pay,
            'total_leave_with_pay' => $salaryStatement->total_leave_with_pay,
            'total_absent' => $salaryStatement->total_absent,

            'taxable' => $taxable,
            'nontaxable' => $nontaxable,
            'contribution' => $contribution,
            'withholding_tax' => $withholding_tax,
            'deduction' => $deduction,
            'net' => $net,

            'statement_attendances' => $statementAttendances,
            'statement_details' => $statementDetails,
        ];
    }
}
