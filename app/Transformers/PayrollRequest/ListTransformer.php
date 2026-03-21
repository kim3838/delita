<?php

namespace App\Transformers\PayrollRequest;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Blueprint\RequestInterface;
use App\Facades\Fractal;
use App\Models\PayrollRequest;
use App\Traits\HasTime;
use App\Transformers\Payroll\BasicTransformer as PayrollBasicTransformer;
use App\Transformers\RequestApprovalState\ListTransformer as RequestApprovalStateListTransformer;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    use HasTime;

    public function transform(PayrollRequest $payrollRequest): array
    {
        $payroll = Fractal::item($payrollRequest->payroll, PayrollBasicTransformer::class);

        $requestInterface = app(RequestInterface::class);

        $accountId = $requestInterface->accountId;
        $filters = $requestInterface->filters;

        $approvalStateFilters = (object)[
            'account_id' => $accountId,
            'associated_companies' => [$filters->company_id],
            'requestable_type' => Relation::getMorphAlias( PayrollRequest::class),
            'requestable_ids' => [$payrollRequest->id],
            'show_only_current_state' => false
        ];

        $companyUserRequestedByHydrated = App::make(CompanyUserRepository::class)->hydrateItem([
            'company_timezone' => $payrollRequest->requested_by_user_company_timezone,
            'is_employee' => $payrollRequest->requested_by_user_is_employee,
            'company_employee_number' => $payrollRequest->requested_by_user_company_employee_number,
            'company_employee_full_name' => $payrollRequest->requested_by_user_company_employee_full_name,

            'user_id' => $payrollRequest->requested_by_user_id,
            'user_username' => $payrollRequest->requested_by_user_username,
        ]);

        $totalBasicGross = BigDecimal::of((string)$payrollRequest->total_basic_gross);
        $totalOtherGross = BigDecimal::of((string)$payrollRequest->total_other_gross);
        $totalTaxable = BigDecimal::of((string)$payrollRequest->total_taxable);
        $totalNontaxable = BigDecimal::of((string)$payrollRequest->total_nontaxable);
        $totalContribution = BigDecimal::of((string)$payrollRequest->total_contribution);
        $totalEmployerContributionShare = BigDecimal::of((string)$payrollRequest->total_employer_contribution_share);
        $totalWithholdingTax = BigDecimal::of((string)$payrollRequest->total_withholding_tax);
        $totalDeduction = BigDecimal::of((string)$payrollRequest->total_deduction);
        $totalNet = BigDecimal::of((string)$payrollRequest->total_net);

        $companyUserRequestedByEmployeeFullName = $companyUserRequestedByHydrated->is_employee
            ? $companyUserRequestedByHydrated->company_employee_full_name
            : null;

        $approvalStates = Fractal::collection(
            App::make(RequestApprovalStateRepository::class)->list($approvalStateFilters),
            RequestApprovalStateListTransformer::class
        )['data'];

        return [
            'row_number' => $payrollRequest->row_number,

            'payroll' => [
                ...$payroll,
                'total_basic_gross' => $totalBasicGross->toScale(4, RoundingMode::HalfUp),
                'total_other_gross' => $totalOtherGross->toScale(4, RoundingMode::HalfUp),
                'total_taxable' => $totalTaxable->toScale(4, RoundingMode::HalfUp),
                'total_nontaxable' => $totalNontaxable->toScale(4, RoundingMode::HalfUp),
                'total_contribution' => $totalContribution->toScale(4, RoundingMode::HalfUp),
                'total_employer_contribution_share' => $totalEmployerContributionShare->toScale(4, RoundingMode::HalfUp),
                'total_withholding_tax' => $totalWithholdingTax->toScale(4, RoundingMode::HalfUp),
                'total_deduction' => $totalDeduction->toScale(4, RoundingMode::HalfUp),
                'total_net' => $totalNet->toScale(4, RoundingMode::HalfUp),
            ],

            'id' => $payrollRequest->id,
            'number' => $payrollRequest->number,
            'requested_by' => [
                'company_employee_number' => $companyUserRequestedByHydrated->company_employee_number,
                'company_employee_full_name' => $companyUserRequestedByEmployeeFullName,

                'username' => $companyUserRequestedByHydrated->user_username,
            ],
            'date_requested_diff' => $this->diffForHumans(
                $payrollRequest->date_requested->shiftTimezone($payrollRequest->company_timezone),
                Carbon::now($payrollRequest->company_timezone)
            ),

            'company_timezone' => $payrollRequest->company_timezone,

            'remarks' => $payrollRequest->remarks,
            'status_summary' => $payrollRequest->status_summary?->toArray(),

            'approval_states' => $approvalStates,
        ];
    }
}
