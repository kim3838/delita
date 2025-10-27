<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BaseAttendanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_in' => 'required|date_format:Y-m-d H:i',
            'lunch_out' => 'sometimes|required|date_format:Y-m-d H:i',
            'lunch_in' => 'sometimes|required|date_format:Y-m-d H:i',
            'last_out' => 'required|date_format:Y-m-d H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'first_in.required' => 'First in is required.',
            'first_in.date_format' => 'First in must match the format Y-m-d H:i e.g.(2000-12-31 09:00).',
            'lunch_out.required' => 'Lunch out is required.',
            'lunch_out.date_format' => 'Lunch out must match the format Y-m-d H:i e.g.(2000-12-31 13:00).',
            'lunch_in.required' => 'Lunch in is required.',
            'lunch_in.date_format' => 'Lunch in must match the format Y-m-d H:i e.g.(2000-12-31 14:00).',
            'last_out.required' => 'Last out is required.',
            'last_out.date_format' => 'Last out must match the format Y-m-d H:i e.g.(2000-12-31 17:00).',
        ];
    }
}
