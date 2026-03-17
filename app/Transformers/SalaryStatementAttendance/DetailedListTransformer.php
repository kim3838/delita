<?php

namespace App\Transformers\SalaryStatementAttendance;

use App\Blueprint\Repositories\SalaryStatementAttendanceDetailRepository;
use App\Blueprint\Repositories\SalaryStatementAttendancePayrollComponentRepository;
use App\Facades\Fractal;
use App\Models\SalaryStatementAttendance;
use App\Transformers\SalaryStatementAttendanceDetail\ListTransformer as SalaryStatementAttendanceDetailListTransformer;
use App\Transformers\SalaryStatementAttendancePayrollComponent\NonComputableListTransformer as SalaryStatementAttendancePayrollComponentNonComputableListTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class DetailedListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendance $salaryStatementAttendance): array
    {
        $salaryStatementId = $salaryStatementAttendance->salary_statement_id;
        $salaryStatementAttendanceId = $salaryStatementAttendance->id;

        $salaryStatementAttendanceRelatedRepositoryFilters = (object)[
            'salary_statement_attendance_ids' => [$salaryStatementAttendanceId],
        ];

        $attendanceDetails = App::make(SalaryStatementAttendanceDetailRepository::class)->list($salaryStatementAttendanceRelatedRepositoryFilters);
        $attendanceDetails = Fractal::collection($attendanceDetails, SalaryStatementAttendanceDetailListTransformer::class)['data'];

        $attendancePayrollComponents = App::make(SalaryStatementAttendancePayrollComponentRepository::class)->list($salaryStatementAttendanceRelatedRepositoryFilters, ['salary_statement_attendance']);
        $attendancePayrollComponents = Fractal::collection($attendancePayrollComponents, SalaryStatementAttendancePayrollComponentNonComputableListTransformer::class)['data'];

        return [
            'row_number' => $salaryStatementAttendance->row_number,
            'id' => $salaryStatementAttendance->id,
            'date' => $salaryStatementAttendance->date->toDateString(),
            'date_readable' => $salaryStatementAttendance->date->format('M d, Y'),
            'week_day_name' => $salaryStatementAttendance->date->format('l'),
            'status' => $salaryStatementAttendance->status?->toArray(),
            'day_type' => $salaryStatementAttendance->day_type?->toArray(),

            'pay_splits' => $attendanceDetails,
            'pay_item_totals' => $attendancePayrollComponents,
        ];
    }
}
