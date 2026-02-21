<?php

namespace App\Transformers\SalaryStatement;

use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\SalaryStatement;
use App\Transformers\Payroll\BasicTransformer as PayrollBasicTransformer;
use App\Transformers\SalaryStatementAttendance\DetailedListTransformer as SalaryStatementAttendanceDetailedListTransformer;
use App\Transformers\SalaryStatementDetail\ListTransformer as SalaryStatementDetailListTransformer;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(SalaryStatement $salaryStatement): array
    {
        $payroll = Fractal::item($salaryStatement->payroll, PayrollBasicTransformer::class);

        $employee = Employee::query()->find($salaryStatement->employee_id);

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
            app(SalaryStatementDetailRepository::class)->list($salaryStatementDetailRepositoryFilters),
            SalaryStatementDetailListTransformer::class
        )['data'];

        $taxable = BigDecimal::of($salaryStatement->taxable);
        $nontaxable = BigDecimal::of($salaryStatement->nontaxable);
        $contribution = BigDecimal::of($salaryStatement->contribution);
        $withholding_tax = BigDecimal::of($salaryStatement->withholding_tax);
        $deduction = BigDecimal::of($salaryStatement->deduction);
        $net = BigDecimal::of($salaryStatement->net);

        return [
            'row_number' => $salaryStatement->row_number,
            'id' => $salaryStatement->id,
            'ulid' => $salaryStatement->ulid,
            'payroll_id' => $salaryStatement->payroll_id,
            'employee_id' => $salaryStatement->employee_id,

            'payroll' => $payroll,

            'employee_number' => $employee->number,
            'employee_full_name' => $employee->full_name,
            'employee_department' => $employee->departments->first(),
            'employee_designation' => $employee->designation,

            'total_days' => $salaryStatement->total_days,
            'total_day_offs' => $salaryStatement->total_day_offs,
            'total_working_days' => $salaryStatement->total_working_days,
            'total_regular_work_days' => $salaryStatement->total_regular_work_days,
            'total_working_rest_days' => $salaryStatement->total_working_rest_days,
            'total_special_holidays' => $salaryStatement->total_special_holidays,
            'total_legal_holidays' => $salaryStatement->total_legal_holidays,
            'total_present' => $salaryStatement->total_full_present + $salaryStatement->total_present_with_irregularity,
            'total_full_present' => $salaryStatement->total_full_present,
            'total_present_with_irregularity' => $salaryStatement->total_present_with_irregularity,
            'total_leaves' => $salaryStatement->total_leave_without_pay + $salaryStatement->total_leave_with_pay,
            'total_leave_without_pay' => $salaryStatement->total_leave_without_pay,
            'total_leave_with_pay' => $salaryStatement->total_leave_with_pay,
            'total_absent' => $salaryStatement->total_absent,

            'taxable' => $taxable->toScale(2, RoundingMode::HalfUp),
            'nontaxable' => $nontaxable->toScale(2, RoundingMode::HalfUp),
            'contribution' => $contribution->toScale(2, RoundingMode::HalfUp),
            'withholding_tax' => $withholding_tax->toScale(2, RoundingMode::HalfUp),
            'deduction' => $deduction->toScale(2, RoundingMode::HalfUp),
            'net' => $net->toScale(2, RoundingMode::HalfUp),

            'statement_attendances' => $statementAttendances,
            'statement_details' => $statementDetails,
        ];
    }
}
