<?php

namespace App\Http\Requests\Shift;

use App\Enums\ShiftType;
use App\Helpers\TimeHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BaseShiftStoreAndUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'integer', Rule::in([ShiftType::REGULAR->value, ShiftType::GRAVEYARD->value])],
            'work_start_grace_time' => ['required', 'integer', 'between:0,30'],
            'require_lunch_time_in_and_out' => ['required', 'boolean'],
            'lunch_start_grace_time' => ['sometimes', 'required', 'integer', 'between:0,30'],
            'max_overtime' => ['required', 'numeric', 'between:0,10'],

            'shift_schedules' => ['required', 'array', 'size:7'],
            'shift_schedules.*.week_day' => ['required', 'integer', 'between:0,6'],
            'shift_schedules.*.is_rest_day' => ['required', 'boolean'],
            'shift_schedules.*.is_day_off' => ['required', 'boolean'],
            'shift_schedules.*.is_flexible' => ['required', 'boolean'],
            'shift_schedules.*.has_lunch_break' => ['required', 'boolean'],

            // Work time fields - conditionally required and validated
            'shift_schedules.*.work_start' => [
                function ($attribute, $value, $fail) {
                    $this->validateWorkTime($attribute, $value, $fail);
                }
            ],
            'shift_schedules.*.work_end' => [
                function ($attribute, $value, $fail) {
                    $this->validateWorkTime($attribute, $value, $fail);
                }
            ],
            'shift_schedules.*.total_work_hours_with_breaks' => [
                function ($attribute, $value, $fail) {
                    $this->validateTotalWorkHours($attribute, $value, $fail);
                }
            ],

            // Lunch break fields
            'shift_schedules.*.lunch_break_start' => [
                function ($attribute, $value, $fail) {
                    $this->validateLunchBreakRequired($attribute, $value, $fail);
                    $this->validateLunchBreakStart($attribute, $value, $fail);
                }
            ],
            'shift_schedules.*.lunch_break_end' => [
                function ($attribute, $value, $fail) {
                    $this->validateLunchBreakRequired($attribute, $value, $fail);
                    $this->validateLunchBreakEnd($attribute, $value, $fail);
                }
            ],
            'shift_schedules.*.total_lunch_break_hours' => [
                function ($attribute, $value, $fail) {
                    $this->validateTotalLunchBreakHours($attribute, $value, $fail);
                }
            ],
        ];
    }

    private function validateWorkTime($attribute, $value, $fail): void
    {
        $index = $this->getScheduleIndex($attribute);
        $schedule = $this->input("shift_schedules.{$index}");
        $weekDayName = $schedule['week_day_name'] ?? 'Unknown';

        // If is_day_off is true, work times should be null
        if ($schedule['is_day_off'] && $value !== null) {
            $fail("{$weekDayName}: Work time should be null when is day off.");
            return;
        }

        // If not day off work times are required
        if (!$schedule['is_day_off'] && $value === null) {
            $fieldName = str_contains($attribute, 'work_start') ? 'Work start time' : 'Work end time';
            $fail("{$weekDayName}: {$fieldName} is required when not a day off.");
            return;
        }

        // If flexible, validate specific times
        if ($schedule['is_flexible']) {
            if (str_contains($attribute, 'work_start') && $value !== '00:00') {
                $fail("{$weekDayName}: Work start time should be 12:00 AM when flexible.");
                return;
            }
            if (str_contains($attribute, 'work_end') && $value !== '00:00') {
                $fail("{$weekDayName}: Work end time should be 12:00 AM when flexible.");
                return;
            }
        }

        if ($value !== null && !$schedule['is_day_off'] && !$schedule['is_flexible']) {
            /**
             * Skip time range validation
             * $this->validateTimeRange($value, $fail, $weekDayName);
             * */
        }
    }

    /**
     * Validate if time range is within specific time as per shift type policy
     *
     * @param $time
     * @param $fail
     * @param $weekDayName
     * @return void
     */
    private function validateTimeRange($time, $fail, $weekDayName): void
    {
        $shiftType = ShiftType::from($this->input('type'));
        $timeCarbon = Carbon::createFromFormat('H:i', $time);

        if ($shiftType === ShiftType::REGULAR) {
            $startLimit = Carbon::createFromFormat('H:i', '06:00');
            $endLimit = Carbon::createFromFormat('H:i', '22:00');

            if ($timeCarbon->lt($startLimit) || $timeCarbon->gt($endLimit)) {
                $fail("{$weekDayName}: Time should be between 06:00 AM and 10:00 PM for regular shift.");
            }
        } elseif ($shiftType === ShiftType::GRAVEYARD) {
            $nightStart = Carbon::createFromFormat('H:i', '22:00');
            $nightEnd = Carbon::createFromFormat('H:i', '08:00')->addDay();

            if (!($timeCarbon->gte($nightStart) || $timeCarbon->lte(Carbon::createFromFormat('H:i', '08:00')))) {
                $fail("{$weekDayName}: Time should be between 10:00 PM and 08:00 AM (of next day) for graveyard shift.");
            }
        }
    }

    private function validateTotalWorkHours($attribute, $value, $fail): void
    {
        $index = $this->getScheduleIndex($attribute);
        $schedule = $this->input("shift_schedules.{$index}");
        $weekDayName = $schedule['week_day_name'] ?? 'Unknown';

        // Should be null for day off
        if ($schedule['is_day_off'] && $value !== null) {
            $fail("{$weekDayName}: Total work hours should be null when is_day_off is true.");
            return;
        }

        // Required for flexible schedules
        if ($schedule['is_flexible'] && $value === null) {
            $fail("{$weekDayName}: Total work hours is required for flexible schedules.");
            return;
        }

        if(!$schedule['is_day_off']){
            if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]|24:00$/', $value)) {
                $fail("{$weekDayName}: Total work hours must be in hour:minute format.");
                return;
            }
        }

        // Validate against work start and end times for non-flexible schedules
        if (!$schedule['is_flexible'] && !$schedule['is_day_off']) {
            $workStart = $schedule['work_start'] ?? null;
            $workEnd = $schedule['work_end'] ?? null;

            if ($workStart && $workEnd && $value) {
                $startTime = Carbon::createFromFormat('H:i', $workStart);
                $endTime = Carbon::createFromFormat('H:i', $workEnd);

                // Handle overnight shifts
                if ($endTime->lte($startTime)) {
                    $endTime->addDay();
                }

                $calculatedMinutes = (int)$startTime->diffInMinutes($endTime);
                $providedMinutes = TimeHelper::timeToMinutes($value);

                if ($calculatedMinutes !== $providedMinutes) {
                    $fail("{$weekDayName}: Total work hours should match the difference between work start and work end times. Expected: " . TimeHelper::minutesToTime($calculatedMinutes) . ", Got: " . $value);
                }
            }
        }
    }

    private function validateLunchBreakRequired($attribute, $value, $fail): void
    {
        $index = $this->getScheduleIndex($attribute);
        $schedule = $this->input("shift_schedules.{$index}");
        $weekDayName = $schedule['week_day_name'] ?? 'Unknown';

        // Skip validation for day off
        if ($schedule['is_day_off']) {
            return;
        }

        $hasLunchBreak = filter_var($schedule['has_lunch_break'], FILTER_VALIDATE_BOOLEAN);

        // If has_lunch_break is true, both lunch_break_start and lunch_break_end are required
        if ($hasLunchBreak) {
            if ($value === null || $value === '') {
                $fieldName = str_contains($attribute, 'lunch_break_start') ? 'Lunch break start time' : 'Lunch break end time';
                $fail("{$weekDayName}: {$fieldName} is required when has lunch break.");
            }
        }

        // If has_lunch_break is false, lunch break times should be null
        if (!$hasLunchBreak && $value !== null && $value !== '') {
            $fieldName = str_contains($attribute, 'lunch_break_start') ? 'Lunch break start time' : 'Lunch break end time';
            $fail("{$weekDayName}: {$fieldName} should be null when has lunch break is false.");
        }
    }

    private function validateLunchBreakStart($attribute, $value, $fail): void
    {
        $index = $this->getScheduleIndex($attribute);
        $schedule = $this->input("shift_schedules.{$index}");
        $weekDayName = $schedule['week_day_name'] ?? 'Unknown';

        // Should be null for day off
        if ($schedule['is_day_off'] && $value !== null) {
            $fail("{$weekDayName}: Lunch break time should be null when is day off.");
            return;
        }

        // If one lunch break time is provided, both should be provided
        $lunchStart = $schedule['lunch_break_start'];
        $lunchEnd = $schedule['lunch_break_end'];

        // Validate lunch break is within work hours
        if ($lunchStart && $lunchEnd && !$schedule['is_day_off']) {
            $workStart = $schedule['work_start'];
            $workEnd = $schedule['work_end'];

            if ($workStart && $workEnd) {
                $workStartTime = Carbon::createFromFormat('H:i', $workStart);
                $workEndTime = Carbon::createFromFormat('H:i', $workEnd);
                $lunchStart = Carbon::createFromFormat('H:i', $lunchStart);

                if ($workEndTime->lte($workStartTime)) {
                    $workEndTime->addDay();
                }

                if ($lunchStart->lt($workStartTime)) {
                    $lunchStart->addDay();
                }

                if ($lunchStart->lt($workStartTime) || $lunchStart->gt($workEndTime)) {
                    $fail("{$weekDayName}: Lunch break start time should be between work start and work end times.");
                }
            }
        }
    }

    private function validateLunchBreakEnd($attribute, $value, $fail): void
    {
        $index = $this->getScheduleIndex($attribute);
        $schedule = $this->input("shift_schedules.{$index}");
        $weekDayName = $schedule['week_day_name'] ?? 'Unknown';

        // Should be null for day off
        if ($schedule['is_day_off'] && $value !== null) {
            $fail("{$weekDayName}: Lunch break time should be null when is day off.");
            return;
        }

        // If one lunch break time is provided, both should be provided
        $lunchStart = $schedule['lunch_break_start'];
        $lunchEnd = $schedule['lunch_break_end'];

        // Validate lunch break is within work hours
        if ($lunchStart && $lunchEnd && !$schedule['is_day_off']) {
            $workStart = $schedule['work_start'];
            $workEnd = $schedule['work_end'];

            if ($workEnd) {
                $workStartTime = Carbon::createFromFormat('H:i', $workStart);
                $workEndTime = Carbon::createFromFormat('H:i', $workEnd);
                $lunchStart = Carbon::createFromFormat('H:i', $lunchStart);
                $lunchEnd = Carbon::createFromFormat('H:i', $lunchEnd);

                if ($workEndTime->lte($workStartTime)) {
                    $workEndTime->addDay();
                }

                if ($lunchStart->lt($workStartTime)) {
                    $lunchStart->addDay();
                }

                if ($lunchEnd->lt($lunchStart)) {
                    $lunchEnd->addDay();
                }

                if ($lunchEnd->lt($lunchStart) || $lunchEnd->gt($workEndTime)) {
                    $fail("{$weekDayName}: Lunch break end time should be between lunch start and work end times.");
                }
            }
        }
    }

    private function validateTotalLunchBreakHours($attribute, $value, $fail): void
    {
        $index = $this->getScheduleIndex($attribute);
        $schedule = $this->input("shift_schedules.{$index}");
        $weekDayName = $schedule['week_day_name'] ?? 'Unknown';

        // Should be null for day off
        if ($schedule['is_day_off'] && $value !== null) {
            $fail("{$weekDayName}: Total lunch break hours should be null when is_day_off is true.");
            return;
        }

        $hasLunchBreak = filter_var($schedule['has_lunch_break'], FILTER_VALIDATE_BOOLEAN);

        if(!$schedule['is_day_off'] && $hasLunchBreak){
            if ($value === null || $value === '') {
                $fail("{$weekDayName}: Total lunch break hours is required when has lunch break.");
                return;
            }

            if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]|24:00$/', $value)) {
                $fail("{$weekDayName}: Total lunch break hours must be in hour:minute format.");
                return;
            }
        }

        $lunchStart = $schedule['lunch_break_start'];
        $lunchEnd = $schedule['lunch_break_end'];

        // If lunch times are provided, validate total matches
        if ($lunchStart && $lunchEnd && $value) {
            $startTime = Carbon::createFromFormat('H:i', $lunchStart);
            $endTime = Carbon::createFromFormat('H:i', $lunchEnd);

            if ($endTime->lte($startTime)) {
                $endTime->addDay();
            }

            $calculatedMinutes = (int) $startTime->diffInMinutes($endTime);
            $providedMinutes = TimeHelper::timeToMinutes($value);

            if ($calculatedMinutes !== $providedMinutes) {
                $fail("{$weekDayName}: Total lunch break hours should match the difference between lunch break start and end times. Expected: " . TimeHelper::minutesToTime($calculatedMinutes) . ", Got: " . $value);
            }
        }

        // If no lunch times but total is provided
        if (!$lunchStart && !$lunchEnd && $value !== null) {
            $fail("{$weekDayName}: Total lunch break hours should be null when no lunch break times are provided.");
        }
    }

    private function getScheduleIndex($attribute): int
    {
        preg_match('/shift_schedules\.(\d+)\./', $attribute, $matches);
        return (int)$matches[1];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Code is required.',
            'code.regex' => 'Code must not contain spaces.',
            'code.unique' => 'Code has already been taken.',
            'name.required' => 'Name is required.',
            'type.required' => 'Type is required.',
            'type.in' => 'Invalid shift type selected.',
            'work_start_grace_time.required' => 'Work start grace time is required.',
            'work_start_grace_time.between' => 'Work start grace time must be between 0 and 30 minutes.',
            'require_lunch_time_in_and_out.required' => 'Require lunch time in and out is required.',
            'lunch_start_grace_time.required' => 'Lunch start grace time is required.',
            'lunch_start_grace_time.between' => 'Lunch start grace time must be between 0 and 30 minutes.',
            'shift_schedules.required' => 'Shift schedules are required.',
            'shift_schedules.size' => 'Shift schedules must contain exactly 7 days.',
        ];
    }
}
