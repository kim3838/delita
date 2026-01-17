<?php

namespace App\Transformers\ShiftAssignment;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\Hydrations\Employee\ShiftAssignment;
use App\Transformers\EmploymentProfile\CurrentEmploymentProfileTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(ShiftAssignment $shiftAssignment): array
    {
        $currentEmploymentProfileHydrated = App::make(EmploymentProfileRepository::class)->hydrateItem([

            'employee_id' => $shiftAssignment->employee_id,
            'is_active' => $shiftAssignment->employee_employment_status_active,
            'status' => $shiftAssignment->employee_current_employment_status,
            'employment_type' => $shiftAssignment->employee_current_employment_type,
        ]);

        $currentEmploymentProfile = Fractal::item($currentEmploymentProfileHydrated, CurrentEmploymentProfileTransformer::class);

        $employee = Employee::query()->find($shiftAssignment->employee_id);

        return [
            'row_number' => $shiftAssignment->row_number,
            'id' => $shiftAssignment->id,
            'employee_shift_id' => $shiftAssignment->employee_shift_id,
            'employee_id' => $shiftAssignment->employee_id,
            'shift_id' => $shiftAssignment->shift_id,

            'employee_number' => $employee->number,
            'employee_full_name' => $employee->full_name,
            'employee_current_employment_profile' => $currentEmploymentProfile,
            'employee_department' => $employee->departments->first(),
            'employee_designation' => $employee->designation,

            'shift_ulid' => $shiftAssignment->shift_ulid,
            'shift_code' => $shiftAssignment->shift_code,
            'shift_name' => $shiftAssignment->shift_name,

            //Shift assignment settings
            'shift_start_date' => $shiftAssignment->shift_start_date?->format('Y-m-d'),
            'shift_stated_shift_end_date' => $shiftAssignment->shift_stated_shift_end_date,
            'shift_end_date' => $shiftAssignment->shift_end_date?->format('Y-m-d'),
        ];
    }
}
