<?php

namespace App\Http\Requests\Attendance;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ImportAttendance extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_in' => [
                'required',
                'date_format:Y-m-d H:i',
            ],
            'lunch_out' => [
                'sometimes',
                'required',
                'date_format:Y-m-d H:i',
            ],
            'lunch_in' => [
                'sometimes',
                'required',
                'date_format:Y-m-d H:i',
            ],
            'last_out' => [
                'required',
                'date_format:Y-m-d H:i',
            ],
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

    public function validateAttendance(
        Carbon $firstIn, ?Carbon $lunchOut, ?Carbon $lunchIn, Carbon $lastOut,
        $schedule = [], $validateLunch = false
    ): array
    {
        $errors = [];

        if(empty($schedule)){return [];}

        /**
         * First in should be lesser than schedule work end,
         * Last out should be greater than schedule work start,
         **/
        if($firstIn->gte($schedule['work_end'])){
            $errors[] = 'First in should be lesser than schedule work end.';
        }

        if($lastOut->lte($schedule['work_start'])){
            $errors[] = 'Last out should be greater than schedule work start.';
        }

        /**
         * If Shift requires lunch out and in
         *
         * Lunch out should be greater than First in and (lesser than Last out and lesser schedule work end)
         * Lunch in should be greater than or equal to Lunch out and (lesser than Last out and lesser schedule work end)
         *
         **/
        if($validateLunch && !empty($lunchOut) && !empty($lunchIn)){

            if($lunchOut->lt($firstIn)){
                $errors[] = 'Lunch out should be greater or equal to First in.';
            }

            if($lunchOut->gte($lastOut) || $lunchOut->gte($schedule['work_end'])){
                $errors[] = 'Lunch out should be lesser than both Last out and Schedule work end.';
            }

            if($lunchIn->lt($lunchOut)){
                $errors[] = 'Lunch in should be greater or equal to Lunch out.';
            }

            if($lunchIn->gte($lastOut) || $lunchIn->gte($schedule['work_end'])) {
                $errors[] = 'Lunch in should be lesser than both Last out and Schedule work end.';
            }
        }

        return $errors;
    }
}
