<?php

namespace App\Transformers\LeaveRequest;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Enums\DepartmentEmployeeAssignmentType;
use App\Facades\Fractal;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Traits\HasTime;
use App\Transformers\LeaveRequestResult\ListTransformer as LeaveRequestResultListTransformer;
use App\Transformers\LeaveType\ItemTransformer as LeaveTypeItemTransformer;
use App\Transformers\RequestApprovalState\ListTransformer as RequestApprovalStateListTransformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    use HasTime;

    public function transform(LeaveRequest $leaveRequest): array
    {
        $leaveType = LeaveType::query()->find($leaveRequest->leave_type_id);

        $leaveType = $leaveType ? Fractal::item($leaveType, LeaveTypeItemTransformer::class) : $leaveType;

        $shift = Shift::query()->find($leaveRequest->shift_id);

        $filters = json_decode(request()->get('filters'));

        $approvalStateFilters = (object)[
            'account_id' => request()->account_id,
            'associated_companies' => [$filters->company_id],
            'requestable_type' => Relation::getMorphAlias( LeaveRequest::class),
            'requestable_ids' => [$leaveRequest->id],
            'show_only_current_state' => false
        ];

        $companyUserRequestedByHydrated = App::make(CompanyUserRepository::class)->hydrateItem([
            'company_timezone' => $leaveRequest->requested_by_user_company_timezone,
            'is_employee' => $leaveRequest->requested_by_user_is_employee,
            'company_employee_number' => $leaveRequest->requested_by_user_company_employee_number,
            'company_employee_full_name' => $leaveRequest->requested_by_user_company_employee_full_name,

            'user_id' => $leaveRequest->requested_by_user_id,
            'user_username' => $leaveRequest->requested_by_user_username,
        ]);

        $companyUserRequestedByEmployeeFullName = $companyUserRequestedByHydrated->is_employee
            ? $companyUserRequestedByHydrated->company_employee_full_name
            : null;

        $approvalStates = Fractal::collection(
            App::make(RequestApprovalStateRepository::class)->list($approvalStateFilters),
            RequestApprovalStateListTransformer::class
        )['data'];

        $results = Fractal::collection($leaveRequest->results, LeaveRequestResultListTransformer::class)['data'];

        return [
            'row_number' => $leaveRequest->row_number,

            'employee' => [
                'id' => $leaveRequest->employee_id,
                'number' => $leaveRequest->employee_number,
                'full_name' => $leaveRequest->employee_full_name,
                'employee_department' => $leaveRequest->employee_department_employee_id
                    ? [
                        'name' => $leaveRequest->employee_department_name,
                        'assignment_type' => DepartmentEmployeeAssignmentType::tryFrom($leaveRequest->employee_department_assignment_type)?->toArray()
                    ] : null,
                'employee_designation' => $leaveRequest->employee_designation_id
                    ? ['name' => $leaveRequest->employee_designation_name]
                    : null,
            ],

            'shift' => [
                'id' => $shift->id,
                'number' => $shift->code,
                'name' => $shift->name,
            ],

            'leave_type' => $leaveType,

            'id' => $leaveRequest->id,
            'number' => $leaveRequest->number,
            'requested_by' => [
                'company_employee_number' => $companyUserRequestedByHydrated->company_employee_number,
                'company_employee_full_name' => $companyUserRequestedByEmployeeFullName,

                'username' => $companyUserRequestedByHydrated->user_username,
            ],
            'date_requested_diff' => $this->diffForHumans(
                $leaveRequest->date_requested->shiftTimezone($leaveRequest->company_timezone),
                Carbon::now($leaveRequest->company_timezone)
            ),

            'company_timezone' => $leaveRequest->company_timezone,
            'date_from' => $leaveRequest->date_from->toDateString(),
            'date_from_readable' => $leaveRequest->date_from->format('M j, Y'),
            'date_to' => $leaveRequest->date_to->toDateString(),
            'date_to_readable' => $leaveRequest->date_to->format('M j, Y'),
            'remarks' => $leaveRequest->remarks,
            'status_summary' => $leaveRequest->status_summary?->toArray(),

            'approval_states' => $approvalStates,

            'results' => $results,
        ];
    }
}
