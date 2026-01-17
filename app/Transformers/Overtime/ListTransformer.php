<?php

namespace App\Transformers\Overtime;

use App\Blueprint\Repositories\ShiftRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Facades\Fractal;
use App\Helpers\TimeHelper;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Transformers\Attendance\BasicTransformer as AttendanceBasicTransformer;
use App\Transformers\Shift\ItemTransformer as ShiftItemTransformer;
use App\Transformers\ShiftSchedule\ListTransformer as ShiftScheduleListTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Overtime $overtime): array
    {
        $employee = Employee::query()->find($overtime->attendance_employee_id);

        $attendance = Fractal::item(
            Attendance::query()->find($overtime->attendance_id),
            AttendanceBasicTransformer::class
        );

        $shiftHydrated = App::make(ShiftRepository::class)->hydrateItem([
            'id' => $overtime->attendance_shift_id,
            'max_overtime' => $overtime->attendance_shift_max_overtime,
        ]);

        $attendanceShift = Fractal::item($shiftHydrated, ShiftItemTransformer::class);

        $shiftScheduleHydrated = App::make(ShiftScheduleRepository::class)->hydrateItem([
            'week_day' => $overtime->attendance_shift_schedule_week_day,
            'is_flexible' => $overtime->attendance_shift_schedule_is_flexible,
            'timezone' => $overtime->attendance_shift_schedule_timezone,
            'work_start' => $overtime->attendance_shift_schedule_work_start,
            'work_end' => $overtime->attendance_shift_schedule_work_end,
            'total_work_hours_with_breaks' => $overtime->attendance_shift_schedule_total_work_hours_with_breaks,
        ]);

        $shiftSchedule = Fractal::item($shiftScheduleHydrated, ShiftScheduleListTransformer::class);

        return [
            'row_number' => $overtime->row_number,
            'id' => $overtime->id,
            'ulid' => $overtime->ulid,
            'attendance_id' => $overtime->attendance_id,
            'start' => $overtime->start->format('Y-m-d H:i'),
            'end' => $overtime->end->format('Y-m-d H:i'),
            'duration' => $overtime->duration,
            'duration_readable' => $overtime->duration > 0 ? TimeHelper::minutesToTime($overtime->duration): '',
            'employee' => [
                'id' => $employee->id,
                'number' => $employee->number,
                'full_name' => $employee->full_name,
                'department' => $employee->departments->first(),
                'designation' => $employee->designation,
            ],
            'attendance' => $attendance,
            'shift' => [
                'id' => $attendanceShift['id'],
                'max_overtime' => $attendanceShift['max_overtime'],
                'max_overtime_readable' => $attendanceShift['max_overtime_readable'],
            ],
            'shift_schedule' => [
                'week_day_name' => $shiftSchedule['week_day_name'],
                'is_flexible' => $shiftSchedule['is_flexible'],
                'timezone' => $shiftSchedule['timezone'],
                'work_start' => $shiftSchedule['work_start'],
                'work_end' => $shiftSchedule['work_end'],
                'total_work_hours_with_breaks' => $shiftSchedule['total_work_hours_with_breaks'],
            ]
        ];
    }
}
