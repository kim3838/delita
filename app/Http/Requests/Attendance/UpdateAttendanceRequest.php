<?php

namespace App\Http\Requests\Attendance;

use App\Blueprint\Repositories\ShiftRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Facades\Fractal;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Traits\WorkPeriod;
use App\Transformers\Shift\PatchableTransformer as ShiftPatchableTransformer;
use App\Transformers\ShiftSchedule\PatchableTransformer as ShiftSchedulePatchableTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class UpdateAttendanceRequest extends BaseAttendanceRequest
{
    use WorkPeriod;

    public function authorize(): bool
    {
        $attendance = Attendance::query()->where('ulid', $this->route('attendanceUlid'))->firstOrFail();

        return $this->user()->can('update', $attendance);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'company_id' => 'required|numeric|integer',
            'employee_id' => [
                'required',
                'numeric',
                'integer',
                function ($attribute, $value, $fail) {

                    $employee = Employee::query()->find($value);

                    if (!$employee) {
                        $fail('Employee not found');
                    }
                }
            ],
            'shift_id' => [
                'required',
                'numeric',
                'integer',
                function ($attribute, $value, $fail) {

                    $date = Carbon::parse($this->input('date'));

                    $shift = Shift::query()->find($value);

                    if (!$shift) {
                        $fail('Shift not found');
                    }

                    $attendance = Attendance::query()->where('ulid', $this->route('attendanceUlid'))->first();

                    if (!$attendance) {
                        $fail('Attendance not found');
                    } else {

                        $this->setShift($shift);

                        /**
                         * After setting up shift,
                         * Get the shift work day by attendance date
                         **/
                        $this->setAttendanceSchedule($date);

                        /**
                         * Validate attendance shift details if still match the current shift and schedule settings
                         * */
                        $currentShift = Fractal::item($this->shift, ShiftPatchableTransformer::class);

                        $currentShiftScheduleHydrated = App::make(ShiftScheduleRepository::class)->hydrateItem($this->attendanceSchedule);
                        $currentShiftSchedule = Fractal::item($currentShiftScheduleHydrated, ShiftSchedulePatchableTransformer::class);

                        $attendanceShiftHydrated = App::make(ShiftRepository::class)->hydrateItem($attendance->shiftDetail->toArray());
                        $attendanceShift = Fractal::item($attendanceShiftHydrated, ShiftPatchableTransformer::class);

                        $attendanceShiftScheduleHydrated = App::make(ShiftScheduleRepository::class)->hydrateItem($attendance->shiftDetail->toArray());
                        $attendanceShiftSchedule = Fractal::item($attendanceShiftScheduleHydrated, ShiftSchedulePatchableTransformer::class);

                        $currentShiftAndAttendanceShiftStillTheSame = collect($currentShift)->except(['id', 'ulid', 'company_id', 'code', 'name'])->toArray()
                            == collect($attendanceShift)->except(['id', 'ulid', 'company_id', 'code', 'name'])->toArray();

                        $currentShiftScheduleAndAttendanceShiftScheduleStillTheSame = collect($currentShiftSchedule)->except(['id', 'shift_id', 'week_day_name'])->toArray()
                            == collect($attendanceShiftSchedule)->except(['id', 'shift_id', 'week_day_name'])->toArray();

                        if(!$currentShiftAndAttendanceShiftStillTheSame){
                            $fail('Shift settings have changed. Please re-import attendance');
                        } else if(!$currentShiftScheduleAndAttendanceShiftScheduleStillTheSame){
                            $fail('Shift schedule settings have changed. Please re-import attendance');
                        } else {

                            /**
                             * Get the schedule for the attendance date
                             **/
                            $schedule = $this->parseSchedule($this->attendanceSchedule, $date);

                            $firstIn = Carbon::parse($this->input('first_in'));
                            $lunchOut = empty($this->input('lunch_out'))? null : Carbon::parse($this->input('lunch_out'));
                            $lunchIn = empty($this->input('lunch_in'))? null : Carbon::parse($this->input('lunch_in'));
                            $lastOut = Carbon::parse($this->input('last_out'));

                            $importAttendance = new ImportAttendance();
                            $attendanceValidationErrors = $importAttendance->validateAttendance(
                                $firstIn, $lunchOut, $lunchIn, $lastOut,
                                $schedule,
                                !$this->attendanceScheduleIsFlexible && $this->shiftRequireLunchOutAndIn && $this->attendanceScheduleHasLunchBreak
                            );

                            foreach($attendanceValidationErrors as $error){
                                $fail($error);
                            }
                        }
                    }
                }
            ],
            'date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'company_id.required' => 'Company is required',
            'employee_id.required' => 'Employee is required',
            'shift_id.required' => 'Attendance shift not found. Please re-import attendance with existing shift assigned to employee.',
        ]);
    }
}
