<?php

namespace App\Http\Requests\LeaveDateRangeInquire;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BaseLeaveDateRangeRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'date_from' => 'required|date|date_format:Y-m-d',
            'date_to' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:date_from'
            ],
        ];

        if ($this->filled('date_from')) {

            $maxDate = Carbon::parse($this->date_from)->addMonth()->format('Y-m-d');

            $rules['date_to'][] = "before_or_equal:{$maxDate}";
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'date_from.date_format' => 'Date from must match the format Y-m-d e.g.(2000-12-31)',
            'date_to.date_format' => 'Date to must match the format Y-m-d e.g.(2000-12-31)',
            'date_to.after_or_equal' => 'Date to must be after or equal to date from',
            'date_to.before_or_equal' => 'Date range must not exceed 1 month',
        ];
    }
}
