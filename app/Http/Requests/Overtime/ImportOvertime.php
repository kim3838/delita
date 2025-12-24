<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class ImportOvertime extends FormRequest
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
            'start.required' => 'Overtime start is required',
            'start.date_format' => 'Overtime start must match the format Y-m-d H:i e.g.(2000-12-31 09:00)',
            'end.required' => 'Overtime end is required',
            'end.date_format' => 'Overtime end must match the format Y-m-d H:i e.g.(2000-12-31 17:00)',
        ];
    }

    public function validateOvertime($attendance, $overtimeStart, $overtimeEnd, $schedule = []): array
    {
        $errors = [];

        if(empty($schedule)){return [];}

        /**
         * Overtime end should not be greater than attendance last out
         **/
        if($overtimeEnd->gt($attendance->last_out)){
            $errors[] = 'Overtime end should not be greater than attendance last out';
        }

        /**
         * Overtime end should be greater than overtime start
         **/
        if($overtimeEnd->lt($overtimeStart)){
            $errors[] = 'Overtime end should be greater than overtime start';
        }

        /**
         * Overtime start should not be greater than or equal attendance last out
         **/
        if($overtimeStart->gte($attendance->last_out)){
            $errors[] = 'Overtime start should be lesser than attendance last out';
        }

        /**
         * Overtime end should not be lesser than the schedule end
         **/
        if($overtimeEnd->lt($schedule['work_end'])){
            $errors[] = 'Overtime end should not be lesser than the schedule end';
        }

        /**
         * Overtime start should not be lesser than the schedule end
         **/
        if($overtimeStart->lt($schedule['work_end'])){
            $errors[] = 'Overtime start should not be lesser than the schedule end';
        }

        return $errors;
    }
}
