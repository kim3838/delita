<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class BaseOvertimeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start' => [
                'required',
                'date_format:Y-m-d H:i',
            ],
            'end' => [
                'required',
                'date_format:Y-m-d H:i',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'start.required' => 'Overtime start is required.',
            'start.date_format' => 'Overtime start must match the format Y-m-d H:i e.g.(2000-12-31 09:00).',
            'end.required' => 'Overtime end is required.',
            'end.date_format' => 'Overtime end must match the format Y-m-d H:i e.g.(2000-12-31 17:00).',
        ];
    }
}
