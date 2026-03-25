<?php

namespace App\Transformers\PayrollRequest;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Blueprint\RequestInterface;
use App\Facades\Fractal;
use App\Facades\MoneyFormat;
use App\Models\PayrollRequest;
use App\Traits\HasTime;
use App\Transformers\Payroll\BasicTransformer as PayrollBasicTransformer;
use App\Transformers\RequestApprovalState\ListTransformer as RequestApprovalStateListTransformer;
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

        $totalBasicGross = MoneyFormat::numberFormat($payrollRequest->total_basic_gross ?? 0);
        $totalOtherGross = MoneyFormat::numberFormat($payrollRequest->total_other_gross ?? 0);
        $totalTaxable = MoneyFormat::numberFormat($payrollRequest->total_taxable ?? 0);
        $totalNontaxable = MoneyFormat::numberFormat($payrollRequest->total_nontaxable ?? 0);
        $totalContribution = MoneyFormat::numberFormat($payrollRequest->total_contribution ?? 0);
        $totalEmployerContributionShare = MoneyFormat::numberFormat($payrollRequest->total_employer_contribution_share ?? 0);
        $totalWithholdingTax = MoneyFormat::numberFormat($payrollRequest->total_withholding_tax ?? 0);
        $totalTaxRefund = MoneyFormat::numberFormat($payrollRequest->total_tax_refund ?? 0);
        $totalDeduction = MoneyFormat::numberFormat($payrollRequest->total_deduction ?? 0);
        $totalNet = MoneyFormat::numberFormat($payrollRequest->total_net ?? 0);

        $totalBasicGrossFormatted = MoneyFormat::toLocale($payrollRequest->total_basic_gross, $payrollRequest->company_currency_code);
        $totalOtherGrossFormatted = MoneyFormat::toLocale($payrollRequest->total_other_gross, $payrollRequest->company_currency_code);
        $totalTaxableFormatted = MoneyFormat::toLocale($payrollRequest->total_taxable, $payrollRequest->company_currency_code);
        $totalNontaxableFormatted = MoneyFormat::toLocale($payrollRequest->total_nontaxable, $payrollRequest->company_currency_code);
        $totalContributionFormatted = MoneyFormat::toLocale($payrollRequest->total_contribution, $payrollRequest->company_currency_code);
        $totalEmployerContributionShareFormatted = MoneyFormat::toLocale($payrollRequest->total_employer_contribution_share, $payrollRequest->company_currency_code);
        $totalWithholdingTaxFormatted = MoneyFormat::toLocale($payrollRequest->total_withholding_tax, $payrollRequest->company_currency_code);
        $totalTaxRefundFormatted = MoneyFormat::toLocale($payrollRequest->total_tax_refund, $payrollRequest->company_currency_code);
        $totalDeductionFormatted = MoneyFormat::toLocale($payrollRequest->total_deduction, $payrollRequest->company_currency_code);
        $totalNetFormatted = MoneyFormat::toLocale($payrollRequest->total_net, $payrollRequest->company_currency_code);

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

                'total_basic_gross' => $totalBasicGross,
                'total_other_gross' => $totalOtherGross,
                'total_taxable' => $totalTaxable,
                'total_nontaxable' => $totalNontaxable,
                'total_contribution' => $totalContribution,
                'total_employer_contribution_share' => $totalEmployerContributionShare,
                'total_withholding_tax' => $totalWithholdingTax,
                'total_tax_refund' => $totalTaxRefund,
                'total_deduction' => $totalDeduction,
                'total_net' => $totalNet,

                'total_basic_gross_formatted' => $totalBasicGrossFormatted,
                'total_other_gross_formatted' => $totalOtherGrossFormatted,
                'total_taxable_formatted' => $totalTaxableFormatted,
                'total_nontaxable_formatted' => $totalNontaxableFormatted,
                'total_contribution_formatted' => $totalContributionFormatted,
                'total_employer_contribution_share_formatted' => $totalEmployerContributionShareFormatted,
                'total_withholding_tax_formatted' => $totalWithholdingTaxFormatted,
                'total_tax_refund_formatted' => $totalTaxRefundFormatted,
                'total_deduction_formatted' => $totalDeductionFormatted,
                'total_net_formatted' => $totalNetFormatted,

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
