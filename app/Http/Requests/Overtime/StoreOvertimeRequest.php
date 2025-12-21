<?php

namespace App\Http\Requests\Overtime;

use App\Models\Attendance;
use App\Models\Overtime;
use App\Traits\WorkPeriod;
use Carbon\Carbon;

class StoreOvertimeRequest extends ImportOvertime
{
    use WorkPeriod;

    public function authorize(): bool
    {
        return $this->user()->can('create', Overtime::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'attendance_id' => 'required|numeric|integer|exists:attendances,id',
            'company_id' => 'required|numeric|integer',
            'date' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {

                    $date = Carbon::parse($value);
                    $attendanceId = $this->input('attendance_id');
                    $overtimeStart = Carbon::parse($this->input('start'));
                    $overtimeEnd = Carbon::parse($this->input('end'));

                    $attendance = Attendance::query()->find($attendanceId);

                    if (!$attendance) {
                        $fail('Attendance not found');
                    } else {

                        $this->setShift($attendance->shift_id);
                        $this->setAttendanceSchedule($date);

                        /**
                         * Validate attendance shift details if still match the current shift and schedule settings
                         * */
                        list(
                            $currentShiftAndAttendanceShiftStillTheSame,
                            $currentShiftScheduleAndAttendanceShiftScheduleStillTheSame
                        ) = $this->validateAttendanceShiftDetails(
                            $this->shift,
                            $this->attendanceSchedule,
                            $attendance->shiftDetail->toArray(),
                            $attendance->shiftDetail->toArray()
                        );

                        if(!$currentShiftAndAttendanceShiftStillTheSame){
                            $fail('Shift settings have changed. Please re-import attendance');
                        } else if(!$currentShiftScheduleAndAttendanceShiftScheduleStillTheSame){
                            $fail('Shift schedule settings have changed. Please re-import attendance');
                        } else {

                            /**
                             * Get the schedule for the attendance date
                             **/
                            $schedule = $this->parseSchedule($this->attendanceSchedule, $date);

                            $overtimeValidatedErrors = $this->validateOvertime($attendance, $overtimeStart, $overtimeEnd, $schedule);

                            foreach($overtimeValidatedErrors as $error){
                                $fail($error);
                            }
                        }
                    }
                }
            ],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'attendance_id.required' => 'Attendance not found',
            'attendance_id.exists' => 'Attendance not found',
            'company_id.required' => 'Company not found',
        ]);
    }
}
