<?php

namespace App\Http\Requests\Attendance;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class ViewAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attendance = Attendance::query()->where('ulid', $this->route('attendanceUlid'))->firstOrFail();

        return $this->user()->can('view', $attendance);
    }
}
