<?php

namespace App\Transformers\SalaryStatement;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Enums\DepartmentEmployeeAssignmentType;
use App\Facades\Fractal;
use App\Models\SalaryStatement;
use App\Transformers\Company\ItemTransformer as CompanyItemTransformer;
use App\Transformers\Payroll\BasicTransformer as PayrollBasicTransformer;
use App\Transformers\SalaryStatementDetail\PayslipTransformer as SalaryStatementDetailPayslipTransformer;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class PayslipTransformer extends TransformerAbstract
{
    public function transform(SalaryStatement $salaryStatement): array
    {
        $payroll = Fractal::item($salaryStatement->payroll, PayrollBasicTransformer::class);

        $company = $salaryStatement->payroll->company;

        $company = Fractal::item($company, CompanyItemTransformer::class);

        $employeeRepositoryFilters = (object)[
            'employee_ids' => [$salaryStatement->employee_id],
        ];

        $employee = App::make(EmployeeRepository::class)->list($employeeRepositoryFilters, ['department', 'designation'])->first();

        $salaryStatementDetailRepositoryFilters = (object)[
            'payroll_ids' => [$salaryStatement->payroll->id],
            'company_ids' => [$salaryStatement->payroll->company_id],
            'employee_ids' => [$salaryStatement->employee_id],
        ];

        $statementDetails = Fractal::collection(
            app(SalaryStatementDetailRepository::class)->list($salaryStatementDetailRepositoryFilters, ['salary_statement']),
            SalaryStatementDetailPayslipTransformer::class
        )['data'];

        $taxable = BigDecimal::of($salaryStatement->taxable);
        $nontaxable = BigDecimal::of($salaryStatement->nontaxable);
        $contribution = BigDecimal::of($salaryStatement->contribution);
        $withholding_tax = BigDecimal::of($salaryStatement->withholding_tax);
        $deduction = BigDecimal::of($salaryStatement->deduction);
        $net = BigDecimal::of($salaryStatement->net);

        return [
            'id' => $salaryStatement->id,
            'ulid' => $salaryStatement->ulid,
            'payroll_id' => $salaryStatement->payroll_id,
            'employee_id' => $salaryStatement->employee_id,
            'type' => $salaryStatement->type?->toArray(),
            'is_paid' => $salaryStatement->is_paid,

            'company' => $company,
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

            'taxable' => $taxable->toScale(2, RoundingMode::HalfUp)->toString(),
            'nontaxable' => $nontaxable->toScale(2, RoundingMode::HalfUp)->toString(),
            'contribution' => $contribution->toScale(2, RoundingMode::HalfUp)->toString(),
            'withholding_tax' => $withholding_tax->toScale(2, RoundingMode::HalfUp)->toString(),
            'deduction' => $deduction->toScale(2, RoundingMode::HalfUp)->toString(),
            'net' => $net->toScale(2, RoundingMode::HalfUp)->toString(),

            'statement_details' => $statementDetails,
        ];
    }
}
