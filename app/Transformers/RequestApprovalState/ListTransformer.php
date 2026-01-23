<?php

namespace App\Transformers\RequestApprovalState;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Facades\Fractal;
use App\Models\RequestApprovalState;
use App\Traits\HasTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    use HasTime;

    public function transform(RequestApprovalState $requestApprovalState): array
    {
        $requestApprovalStateHydrated = App::make(RequestApprovalStateRepository::class)->hydrateItem([
            'order' => $requestApprovalState->request_approval_state_order,
            'approver_id' => $requestApprovalState->request_approval_state_approver_id,
            'approved_by' => $requestApprovalState->request_approval_state_approved_by,
            'remarks' => $requestApprovalState->request_approval_state_remarks,
            'status' => $requestApprovalState->request_approval_state_status,
            'approved_at' => $requestApprovalState->request_approval_state_approved_at,
        ]);

        $basicRequestApprovalState = Fractal::item($requestApprovalStateHydrated, BasicListTransformer::class);

        $companyUserApproverHydrated = App::make(CompanyUserRepository::class)->hydrateItem([
            'user_id' => $requestApprovalState->user_id,
            'user_ulid' => $requestApprovalState->user_ulid,
            'user_username' => $requestApprovalState->user_username,
            'user_email'=> $requestApprovalState->user_email,
            'user_status' => $requestApprovalState->user_status,
            'user_email_verified_at' => $requestApprovalState->user_email_verified_at,
            'user_timezone' => $requestApprovalState->user_timezone,

            'company_id' => $requestApprovalState->company_id,
            'company_name' => $requestApprovalState->company_name,
            'company_timezone' => $requestApprovalState->company_timezone,
            'company_assignment_type' => $requestApprovalState->company_assignment_type,
            'is_employee' => $requestApprovalState->is_employee,
            'company_employee_number' => $requestApprovalState->company_employee_number,
            'company_employee_family_name' => $requestApprovalState->company_employee_family_name,
            'company_employee_middle_name' => $requestApprovalState->company_employee_middle_name,
            'company_employee_given_name' => $requestApprovalState->company_employee_given_name
        ]);

        $companyUserApproverEmployeeFullName = implode(' ', array_filter([
            $companyUserApproverHydrated->company_employee_family_name,
            $companyUserApproverHydrated->company_employee_given_name,
            $companyUserApproverHydrated->company_employee_middle_name
        ]));
        $companyUserApproverEmployeeFullName = $companyUserApproverHydrated->is_employee ? $companyUserApproverEmployeeFullName : null;

        $companyUserApprovedByHydrated = App::make(CompanyUserRepository::class)->hydrateItem([
            'company_timezone' => $requestApprovalState->approved_by_user_company_timezone,
            'is_employee' => $requestApprovalState->approved_by_user_is_employee,
            'company_employee_number' => $requestApprovalState->approved_by_user_company_employee_number,
            'company_employee_family_name' => $requestApprovalState->approved_by_user_company_employee_family_name,
            'company_employee_middle_name' => $requestApprovalState->approved_by_user_company_employee_middle_name,
            'company_employee_given_name' => $requestApprovalState->approved_by_user_company_employee_given_name,

            'user_id' => $requestApprovalState->approved_by_user_id,
            'user_username' => $requestApprovalState->approved_by_user_username,
        ]);

        $companyUserApprovedByEmployeeFullName = implode(' ', array_filter([
            $companyUserApprovedByHydrated->company_employee_family_name,
            $companyUserApprovedByHydrated->company_employee_given_name,
            $companyUserApprovedByHydrated->company_employee_middle_name
        ]));
        $companyUserApprovedByEmployeeFullName = $companyUserApprovedByHydrated->is_employee ? $companyUserApprovedByEmployeeFullName : null;

        return [
            'row_number' => $requestApprovalState->row_number,
            'id' => $requestApprovalState->id,
            'requestable' => [
                'type' => $requestApprovalState->requestable_type,
                'id' => $requestApprovalState->requestable_id,
                'number' => $requestApprovalState->requestable_number,
                'date_requested_diff' => $this->diffForHumans(
                    $requestApprovalState->requestable_date_requested->shiftTimezone($requestApprovalState->company_timezone),
                    Carbon::now($requestApprovalState->company_timezone)
                ),
                'company_timezone' => $requestApprovalState->company_timezone,
            ],
            'order' => $basicRequestApprovalState['order'],
            'remarks' => $basicRequestApprovalState['remarks'],
            'status' => $basicRequestApprovalState['status'],
            'current_state_flag' => $requestApprovalState->request_approval_state_current_state_flag,
            'current_state_message' => $requestApprovalState->request_approval_state_current_state_flag ? 'Awaiting' : '',
            'approver' => [
                'company_name' => $companyUserApproverHydrated->company_name,
                'company_assignment_type' => $companyUserApproverHydrated->company_assignment_type?->toArray(),
                'is_employee' => $companyUserApproverHydrated->is_employee,
                'company_employee_number' => $companyUserApproverHydrated->company_employee_number,
                'company_employee_full_name' => $companyUserApproverEmployeeFullName,

                'status' => $companyUserApproverHydrated->user_status?->toArray(),
                'user_id' => $companyUserApproverHydrated->user_id,
                'username' => $companyUserApproverHydrated->user_username,
            ],
            'approved_by' => [
                'company_employee_number' => $companyUserApprovedByHydrated->company_employee_number,
                'company_employee_full_name' => $companyUserApprovedByEmployeeFullName,

                'username' => $companyUserApprovedByHydrated->user_username,
                'approved_at' => $basicRequestApprovalState['approved_at']?->toDateTimeString(),
                'company_timezone' => $companyUserApprovedByHydrated->company_timezone,

                'approved_at_diff' => !empty($basicRequestApprovalState['approved_at']) ? $this->diffForHumans(
                    $basicRequestApprovalState['approved_at']->shiftTimezone($requestApprovalState->company_timezone),
                    Carbon::now($requestApprovalState->company_timezone)
                ): null,
            ]
        ];
    }
}
