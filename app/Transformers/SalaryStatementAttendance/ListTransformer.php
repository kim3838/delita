<?php

namespace App\Transformers\SalaryStatementAttendance;

use App\Models\SalaryStatementAttendance;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        return [
            'id' => $salaryStatementAttendance->id,
            'date' => $salaryStatementAttendance->date?->format('F j, Y'),
            'week_day_name' => $salaryStatementAttendance->date?->format('l'),
            'status' => $salaryStatementAttendance->status?->toArray(),
            'day_type' => $salaryStatementAttendance->day_type?->toArray(),
        ];
    }
}
