<?php

namespace App\Transformers\OvertimeRequest;

use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Facades\Fractal;
use App\Helpers\TimeHelper;
use App\Models\OvertimeRequest;
use App\Traits\HasTime;
use App\Transformers\Attendance\ItemTransformer as AttendanceItemTransformer;
use App\Transformers\RequestApprovalState\ListTransformer as RequestApprovalStateListTransformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    use HasTime;

    public function transform(OvertimeRequest $overtimeRequest): array
    {
        $attendanceHydrated = App::make(AttendanceRepository::class)->hydrateItem([
            'id' => $overtimeRequest->attendance_id,
            'ulid' => $overtimeRequest->attendance_ulid,
            'employee_id' => $overtimeRequest->attendance_employee_id,
            'shift_id' => $overtimeRequest->attendance_shift_id,
            'date' => $overtimeRequest->attendance_date,
            'first_in' => $overtimeRequest->attendance_first_in,
            'lunch_out' => $overtimeRequest->attendance_lunch_out,
            'lunch_in' => $overtimeRequest->attendance_lunch_in,
            'last_out' => $overtimeRequest->attendance_last_out,
            'status' => $overtimeRequest->attendance_status,

            'shift_code' => $overtimeRequest->attendance_shift_code,
            'shift_name' => $overtimeRequest->attendance_shift_name,
            'shift_type' => $overtimeRequest->attendance_shift_type,
            'shift_holiday_policy' => $overtimeRequest->attendance_shift_holiday_policy,
            'shift_work_start_grace_time' => $overtimeRequest->attendance_shift_work_start_grace_time,
            'shift_require_lunch_time_in_and_out' => $overtimeRequest->attendance_shift_require_lunch_time_in_and_out,
            'shift_lunch_start_grace_time' => $overtimeRequest->attendance_shift_lunch_start_grace_time,
            'shift_max_overtime' => $overtimeRequest->attendance_shift_max_overtime,

            'shift_assignment_start_date' => $overtimeRequest->attendance_shift_assignment_start_date,
            'shift_assignment_stated_shift_end_date' => $overtimeRequest->attendance_shift_assignment_stated_shift_end_date,
            'shift_assignment_end_date' => $overtimeRequest->attendance_shift_assignment_end_date,

            'shift_schedule_week_day' => $overtimeRequest->attendance_shift_schedule_week_day,
            'shift_schedule_is_rest_day' => $overtimeRequest->attendance_shift_schedule_is_rest_day,
            'shift_schedule_is_day_off' => $overtimeRequest->attendance_shift_schedule_is_day_off,
            'shift_schedule_is_flexible' => $overtimeRequest->attendance_shift_schedule_is_flexible,
            'shift_schedule_timezone' => $overtimeRequest->attendance_shift_schedule_timezone,
            'shift_schedule_work_start' => $overtimeRequest->attendance_shift_schedule_work_start,
            'shift_schedule_work_end' => $overtimeRequest->attendance_shift_schedule_work_end,
            'shift_schedule_total_work_hours_with_breaks' => $overtimeRequest->attendance_shift_schedule_total_work_hours_with_breaks,
            'shift_schedule_has_lunch_break' => $overtimeRequest->attendance_shift_schedule_has_lunch_break,
            'shift_schedule_lunch_break_start' => $overtimeRequest->attendance_shift_schedule_lunch_break_start,
            'shift_schedule_lunch_break_end' => $overtimeRequest->attendance_shift_schedule_lunch_break_end,
            'shift_schedule_total_lunch_break_hours' => $overtimeRequest->attendance_shift_schedule_total_lunch_break_hours,
        ]);

        $attendance = Fractal::item($attendanceHydrated, AttendanceItemTransformer::class);

        $filters = json_decode(request()->get('filters'));

        $approvalStateFilters = (object)[
            'account_id' => request()->account_id,
            'associated_companies' => [$filters->company_id],
            'requestable_type' => Relation::getMorphAlias( OvertimeRequest::class),
            'requestable_ids' => [$overtimeRequest->id],
            'show_only_current_state' => false
        ];

        $companyUserRequestedByHydrated = App::make(CompanyUserRepository::class)->hydrateItem([
            'company_timezone' => $overtimeRequest->requested_by_user_company_timezone,
            'is_employee' => $overtimeRequest->requested_by_user_is_employee,
            'company_employee_number' => $overtimeRequest->requested_by_user_company_employee_number,
            'company_employee_full_name' => $overtimeRequest->requested_by_user_company_employee_full_name,

            'user_id' => $overtimeRequest->requested_by_user_id,
            'user_username' => $overtimeRequest->requested_by_user_username,
        ]);

        $companyUserRequestedByEmployeeFullName = $companyUserRequestedByHydrated->is_employee
            ? $companyUserRequestedByHydrated->company_employee_full_name
            : null;

        $approvalStates = Fractal::collection(
            App::make(RequestApprovalStateRepository::class)->list($approvalStateFilters),
            RequestApprovalStateListTransformer::class
        )['data'];

        return [
            'row_number' => $overtimeRequest->row_number,

            'attendance' => $attendance,

            'id' => $overtimeRequest->id,
            'number' => $overtimeRequest->number,
            'requested_by' => [
                'company_employee_number' => $companyUserRequestedByHydrated->company_employee_number,
                'company_employee_full_name' => $companyUserRequestedByEmployeeFullName,

                'username' => $companyUserRequestedByHydrated->user_username,
            ],
            'date_requested_diff' => $this->diffForHumans(
                $overtimeRequest->date_requested->shiftTimezone($overtimeRequest->company_timezone),
                Carbon::now($overtimeRequest->company_timezone)
            ),

            'company_timezone' => $overtimeRequest->company_timezone,
            'attendance_id' => $overtimeRequest->attendance_id,
            'start' => $overtimeRequest->start->format('Y-m-d H:i'),
            'end' => $overtimeRequest->end->format('Y-m-d H:i'),
            'duration' => $overtimeRequest->duration,
            'duration_readable' => $overtimeRequest->duration > 0 ? TimeHelper::minutesToTime($overtimeRequest->duration): '',
            'remarks' => $overtimeRequest->remarks,
            'status_summary' => $overtimeRequest->status_summary?->toArray(),

            'approval_states' => $approvalStates
        ];
    }
}
