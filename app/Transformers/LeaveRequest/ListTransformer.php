<?php

namespace App\Transformers\LeaveRequest;

use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Traits\HasTime;
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
        $employee = Employee::query()->find($leaveRequest->employee_id);

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

        $approvalStates = Fractal::collection(
            App::make(RequestApprovalStateRepository::class)->list($approvalStateFilters),
            RequestApprovalStateListTransformer::class
        )['data'];

        return [
            'row_number' => $leaveRequest->row_number,

            'employee' => [
                'id' => $employee->id,
                'number' => $employee->number,
                'full_name' => $employee->full_name,
                'department' => $employee->departments->first(),
                'designation' => $employee->designation,
            ],

            'shift' => [
                'id' => $shift->id,
                'number' => $shift->code,
                'name' => $shift->name,
            ],

            'leave_type' => $leaveType,

            'id' => $leaveRequest->id,
            'number' => $leaveRequest->number,
            'requested_by' => $leaveRequest->requestedBy,
            'date_requested_diff' => $this->diffForHumans(
                $leaveRequest->date_requested->shiftTimezone($leaveRequest->company_timezone),
                Carbon::now($leaveRequest->company_timezone)
            ),

            'company_timezone' => $leaveRequest->company_timezone,
            'date_from' => $leaveRequest->date_from->toDateString(),
            'date_to' => $leaveRequest->date_to->toDateString(),
            'remarks' => $leaveRequest->remarks,
            'status_summary' => $leaveRequest->status_summary?->toArray(),

            'approval_states' => $approvalStates
        ];
    }
}
