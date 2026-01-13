<?php

namespace App\Http\Requests\EmployeePortal\Attendance;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class ViewAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attendance = Attendance::query()->where('ulid', $this->route('attendanceUlid'))->firstOrFail();

        return $attendance instanceof Attendance;
    }
}
