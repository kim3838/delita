<?php

namespace App\Transformers\AttendanceAdjustmentRequest;

use App\Blueprint\Repositories\AttendanceRepository;
use App\Facades\Fractal;
use App\Models\AttendanceAdjustmentRequest;
use App\Transformers\Attendance\ItemTransformer as AttendanceItemTransformer;
use App\Transformers\RequestApprovalState\BasicListTransformer as RequestApprovalStateBasicListTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(AttendanceAdjustmentRequest $attendanceAdjustmentRequest): array
    {
        $attendanceHydrated = App::make(AttendanceRepository::class)->hydrateItem([
            'id' => $attendanceAdjustmentRequest->attendance_id,
            'ulid' => $attendanceAdjustmentRequest->attendance_ulid,
            'employee_id' => $attendanceAdjustmentRequest->attendance_employee_id,
            'shift_id' => $attendanceAdjustmentRequest->attendance_shift_id,
            'date' => $attendanceAdjustmentRequest->attendance_date,
            'first_in' => $attendanceAdjustmentRequest->attendance_first_in,
            'lunch_out' => $attendanceAdjustmentRequest->attendance_lunch_out,
            'lunch_in' => $attendanceAdjustmentRequest->attendance_lunch_in,
            'last_out' => $attendanceAdjustmentRequest->attendance_last_out,
            'status' => $attendanceAdjustmentRequest->attendance_status,

            'shift_code' => $attendanceAdjustmentRequest->attendance_shift_code,
            'shift_name' => $attendanceAdjustmentRequest->attendance_shift_name,
            'shift_type' => $attendanceAdjustmentRequest->attendance_shift_type,
            'shift_holiday_policy' => $attendanceAdjustmentRequest->attendance_shift_holiday_policy,
            'shift_work_start_grace_time' => $attendanceAdjustmentRequest->attendance_shift_work_start_grace_time,
            'shift_require_lunch_time_in_and_out' => $attendanceAdjustmentRequest->attendance_shift_require_lunch_time_in_and_out,
            'shift_lunch_start_grace_time' => $attendanceAdjustmentRequest->attendance_shift_lunch_start_grace_time,
            'shift_max_overtime' => $attendanceAdjustmentRequest->attendance_shift_max_overtime,

            'shift_assignment_start_date' => $attendanceAdjustmentRequest->attendance_shift_assignment_start_date,
            'shift_assignment_stated_shift_end_date' => $attendanceAdjustmentRequest->attendance_shift_assignment_stated_shift_end_date,
            'shift_assignment_end_date' => $attendanceAdjustmentRequest->attendance_shift_assignment_end_date,

            'shift_schedule_week_day' => $attendanceAdjustmentRequest->attendance_shift_schedule_week_day,
            'shift_schedule_is_rest_day' => $attendanceAdjustmentRequest->attendance_shift_schedule_is_rest_day,
            'shift_schedule_is_day_off' => $attendanceAdjustmentRequest->attendance_shift_schedule_is_day_off,
            'shift_schedule_is_flexible' => $attendanceAdjustmentRequest->attendance_shift_schedule_is_flexible,
            'shift_schedule_timezone' => $attendanceAdjustmentRequest->attendance_shift_schedule_timezone,
            'shift_schedule_work_start' => $attendanceAdjustmentRequest->attendance_shift_schedule_work_start,
            'shift_schedule_work_end' => $attendanceAdjustmentRequest->attendance_shift_schedule_work_end,
            'shift_schedule_total_work_hours_with_breaks' => $attendanceAdjustmentRequest->attendance_shift_schedule_total_work_hours_with_breaks,
            'shift_schedule_has_lunch_break' => $attendanceAdjustmentRequest->attendance_shift_schedule_has_lunch_break,
            'shift_schedule_lunch_break_start' => $attendanceAdjustmentRequest->attendance_shift_schedule_lunch_break_start,
            'shift_schedule_lunch_break_end' => $attendanceAdjustmentRequest->attendance_shift_schedule_lunch_break_end,
            'shift_schedule_total_lunch_break_hours' => $attendanceAdjustmentRequest->attendance_shift_schedule_total_lunch_break_hours,
        ]);

        $attendance = Fractal::item($attendanceHydrated, AttendanceItemTransformer::class);

        return [
            'row_number' => $attendanceAdjustmentRequest->row_number,

            'attendance' => $attendance,

            'id' => $attendanceAdjustmentRequest->id,
            'number' => $attendanceAdjustmentRequest->number,
            'requested_by' => $attendanceAdjustmentRequest->requestedBy,
            'date_requested' => $attendanceAdjustmentRequest->date_requested->format('Y-m-d H:i'),
            'attendance_id' => $attendanceAdjustmentRequest->attendance_id,
            'first_in' => $attendanceAdjustmentRequest->first_in->format('Y-m-d H:i'),
            'lunch_out' => $attendanceAdjustmentRequest->lunch_out?->format('Y-m-d H:i'),
            'lunch_in' => $attendanceAdjustmentRequest->lunch_in?->format('Y-m-d H:i'),
            'last_out' => $attendanceAdjustmentRequest->last_out->format('Y-m-d H:i'),
            'reason' => $attendanceAdjustmentRequest->reason,
            'status_summary' => $attendanceAdjustmentRequest->status_summary?->toArray(),

            'approval_states' => Fractal::collection(
                $attendanceAdjustmentRequest->approvalStates,
                RequestApprovalStateBasicListTransformer::class
            )['data']
        ];
    }
}
