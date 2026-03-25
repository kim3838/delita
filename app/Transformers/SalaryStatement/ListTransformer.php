<?php

namespace App\Transformers\SalaryStatement;

use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Facades\Fractal;
use App\Facades\MoneyFormat;
use App\Models\SalaryStatement;
use App\Transformers\Payroll\BasicTransformer as PayrollBasicTransformer;
use App\Transformers\SalaryStatementAttendance\BasicListTransformer as SalaryStatementAttendanceBasicListTransformer;
use App\Transformers\SalaryStatementDetail\ListTransformer as SalaryStatementDetailListTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatement $salaryStatement): array
    {
        $payrollHydrated = App::make(PayrollRepository::class)->hydrateItem([
            'id' => $salaryStatement->payroll_id,
            'company_id' => $salaryStatement->payroll_company_id,
            'number' => $salaryStatement->payroll_number,
            'year' => $salaryStatement->payroll_year,
            'month' => $salaryStatement->payroll_month,
            'pay_frequency' => $salaryStatement->payroll_pay_frequency,
            'frequency_sequence' => $salaryStatement->payroll_frequency_sequence,
            'start_date' => $salaryStatement->payroll_start_date,
            'end_date' => $salaryStatement->payroll_end_date,
            'remarks' => $salaryStatement->payroll_remarks,
            'status' => $salaryStatement->payroll_status,
        ]);

        $payroll = Fractal::item($payrollHydrated, PayrollBasicTransformer::class);

        $salaryStatementAttendanceRepositoryFilters = (object)[
            'salary_statement_ids' => [$salaryStatement->id],
        ];

        $salaryStatementAttendances = App::make(SalaryStatementAttendanceRepository::class)->list($salaryStatementAttendanceRepositoryFilters);

        $statementAttendances = Fractal::collection($salaryStatementAttendances, SalaryStatementAttendanceBasicListTransformer::class)['data'];

        $salaryStatementDetailRepositoryFilters = (object)[
            'payroll_ids' => [$salaryStatement->payroll->id],
            'company_ids' => [$payrollHydrated->company_id],
            'employee_ids' => [$salaryStatement->employee_id],
        ];

        $statementDetails = Fractal::collection(
            app(SalaryStatementDetailRepository::class)->list($salaryStatementDetailRepositoryFilters, ['salary_statement']),
            SalaryStatementDetailListTransformer::class
        )['data'];

        $basicGross = MoneyFormat::numberFormat($salaryStatement->total_basic_gross);
        $otherGross = MoneyFormat::numberFormat($salaryStatement->total_other_gross);
        $taxable = MoneyFormat::numberFormat($salaryStatement->taxable);
        $nontaxable = MoneyFormat::numberFormat($salaryStatement->nontaxable);
        $contribution = MoneyFormat::numberFormat($salaryStatement->contribution);
        $withholding_tax = MoneyFormat::numberFormat($salaryStatement->withholding_tax);
        $deduction = MoneyFormat::numberFormat($salaryStatement->deduction);
        $net = MoneyFormat::numberFormat($salaryStatement->net);

        return [
            'row_number' => $salaryStatement->row_number,
            'id' => $salaryStatement->id,
            'ulid' => $salaryStatement->ulid,
            'payroll_id' => $salaryStatement->payroll_id,
            'employee_id' => $salaryStatement->employee_id,
            'type' => $salaryStatement->type?->toArray(),
            'is_paid' => $salaryStatement->is_paid,

            'payroll' => $payroll,

            'company_currency_code' => $salaryStatement->company_currency_code,
            'employee_number' => $salaryStatement->employee_number,
            'employee_full_name' => $salaryStatement->employee_full_name,

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

            'basic_gross' => $basicGross,
            'other_gross' => $otherGross,
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
