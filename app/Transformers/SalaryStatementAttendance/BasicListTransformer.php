<?php

namespace App\Transformers\SalaryStatementAttendance;

use App\Models\SalaryStatementAttendance;
use League\Fractal\TransformerAbstract;

class BasicListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        return [
            'row_number' => $salaryStatementAttendance->row_number,
            'id' => $salaryStatementAttendance->id,
            'date' => $salaryStatementAttendance->date->toDateString(),
            'date_readable' => $salaryStatementAttendance->date->format('M d, Y'),
            'week_day_name' => $salaryStatementAttendance->date?->format('l'),
            'status' => $salaryStatementAttendance->status?->toArray(),
            'day_type' => $salaryStatementAttendance->day_type?->toArray(),
        ];
    }
}
