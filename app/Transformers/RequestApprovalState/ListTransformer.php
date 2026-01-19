<?php

namespace App\Transformers\RequestApprovalState;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Facades\Fractal;
use App\Models\RequestApprovalState;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(RequestApprovalState $requestApprovalState): array
    {
        $requestApprovalStateHydrated = App::make(RequestApprovalStateRepository::class)->hydrateItem([
            'order' => $requestApprovalState->request_approval_state_order,
            'approver_id' => $requestApprovalState->request_approval_state_approver_id,
            'remarks' => $requestApprovalState->request_approval_state_remarks,
            'status' => $requestApprovalState->request_approval_state_status,
        ]);

        $basicRequestApprovalState = Fractal::item($requestApprovalStateHydrated, BasicListTransformer::class);

        $companyUserHydrated = App::make(CompanyUserRepository::class)->hydrateItem([
            'user_id' => $requestApprovalState->user_id,
            'user_ulid' => $requestApprovalState->user_ulid,
            'user_username' => $requestApprovalState->user_username,
            'user_email'=> $requestApprovalState->user_email,
            'user_status' => $requestApprovalState->user_status,
            'user_email_verified_at' => $requestApprovalState->user_email_verified_at,
            'user_timezone' => $requestApprovalState->user_timezone,

            'company_id' => $requestApprovalState->company_id,
            'company_name' => $requestApprovalState->company_name,
            'company_assignment_type' => $requestApprovalState->company_assignment_type,
            'is_employee' => $requestApprovalState->is_employee,
            'company_employee_number' => $requestApprovalState->company_employee_number,
            'company_employee_family_name' => $requestApprovalState->company_employee_family_name,
            'company_employee_middle_name' => $requestApprovalState->company_employee_middle_name,
            'company_employee_given_name' => $requestApprovalState->company_employee_given_name
        ]);

        $employeeFullName = implode(' ', array_filter([
            $companyUserHydrated->company_employee_family_name,
            $companyUserHydrated->company_employee_given_name,
            $companyUserHydrated->company_employee_middle_name
        ]));
        $employeeFullName = $companyUserHydrated->is_employee ? $employeeFullName : null;

        return [
            'row_number' => $requestApprovalState->row_number,
            'id' => $requestApprovalState->id,
            'requestable' => [
                'type' => $requestApprovalState->requestable_type,
                'id' => $requestApprovalState->requestable_id,
                'number' => $requestApprovalState->requestable_number,
            ],
            ...$basicRequestApprovalState,
            'current_state_flag' => $requestApprovalState->request_approval_state_current_state_flag,
            'current_state_message' => $requestApprovalState->request_approval_state_current_state_flag ? 'Awaiting' : '',
            'approver' => [
                'company_name' => $companyUserHydrated->company_name,
                'company_assignment_type' => $companyUserHydrated->company_assignment_type?->toArray(),
                'is_employee' => $companyUserHydrated->is_employee,
                'company_employee_number' => $companyUserHydrated->company_employee_number,
                'company_employee_full_name' => $employeeFullName,

                'status' => $companyUserHydrated->user_status?->toArray(),
                'user_id' => $companyUserHydrated->user_id,
                'username' => $companyUserHydrated->user_username,
            ],
        ];
    }
}
