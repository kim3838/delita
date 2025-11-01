<?php

namespace App\Transformers\AttendanceDetail;

use App\Helpers\TimeHelper;
use App\Models\AttendanceDetail;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(AttendanceDetail $attendanceDetail): array
    {
        return [
            'date' => $attendanceDetail->date->toDateString(),
            'split_type' => $attendanceDetail->split_type->toArray(),
            'split_start' => $attendanceDetail->split_start,
            'split_end' => $attendanceDetail->split_end,
            'split_duration' => TimeHelper::minutesToTime($attendanceDetail->split_duration),
            'work_hour_type' => $attendanceDetail->work_hour_type->toArray(),
            'hourly_rate_type' => $attendanceDetail->hourly_rate_type->toArray(),
            'hourly_rate_multiplier' => $attendanceDetail->hourly_rate_multiplier,
            'base_rate_multiplier' => $attendanceDetail->base_rate_multiplier,
            'order' => $attendanceDetail->order,
            'actual_start' => empty($attendanceDetail->actual_start)
                ? null
                : Carbon::parse($attendanceDetail->actual_start)->format('Y-m-d H:i'),
            'actual_end' => empty($attendanceDetail->actual_end)
                ? null
                : Carbon::parse($attendanceDetail->actual_end)->format('Y-m-d H:i'),
            'grace_before_start_applied' => $attendanceDetail->grace_before_start_applied,
            'grace_after_start_applied' => $attendanceDetail->grace_after_start_applied,
            'first_in' => $attendanceDetail->first_in,
            'lunch_out' => $attendanceDetail->lunch_out,
            'lunch_in' => $attendanceDetail->lunch_in,
            'last_out' => $attendanceDetail->last_out,
            'overtime_start' => $attendanceDetail->overtime_start,
            'overtime_end' => $attendanceDetail->overtime_end,
            'actual_present_start' => empty($attendanceDetail->actual_present_start)
                ? null
                : Carbon::parse($attendanceDetail->actual_present_start)->format('Y-m-d H:i'),
            'actual_present_end' => empty($attendanceDetail->actual_present_end)
                ? null
                : Carbon::parse($attendanceDetail->actual_present_end)->format('Y-m-d H:i'),
            'actual_present' => TimeHelper::minutesToTime($attendanceDetail->actual_present),
            'actual_irregularity_duration_start' => empty($attendanceDetail->actual_irregularity_duration_start)
                ? null
                : Carbon::parse($attendanceDetail->actual_irregularity_duration_start)->format('Y-m-d H:i'),
            'actual_irregularity_duration_end' => empty($attendanceDetail->actual_irregularity_duration_end)
                ? null
                : Carbon::parse($attendanceDetail->actual_irregularity_duration_end)->format('Y-m-d H:i'),
            'actual_irregularity_duration' => TimeHelper::minutesToTime($attendanceDetail->actual_irregularity_duration),
            'late' => TimeHelper::minutesToTime($attendanceDetail->late),
            'undertime' => TimeHelper::minutesToTime($attendanceDetail->undertime),
            'flexible_undertime' => TimeHelper::minutesToTime($attendanceDetail->flexible_undertime),
        ];
    }
}
