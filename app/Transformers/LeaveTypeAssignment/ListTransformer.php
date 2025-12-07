<?php

namespace App\Transformers\LeaveTypeAssignment;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\Hydrations\Employee\LeaveTypeAssignment;
use App\Transformers\EmploymentProfile\CurrentEmploymentProfileTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(LeaveTypeAssignment $leaveTypeAssignment): array
    {
        $currentEmploymentProfileHydrated = App::make(EmploymentProfileRepository::class)->hydrateItem([

            'employee_id' => $leaveTypeAssignment->employee_id,
            'is_active' => $leaveTypeAssignment->employee_employment_status_active,
            'status' => $leaveTypeAssignment->employee_current_employment_status,
            'employment_type' => $leaveTypeAssignment->employee_current_employment_type,
        ]);

        $currentEmploymentProfile = Fractal::item($currentEmploymentProfileHydrated, CurrentEmploymentProfileTransformer::class);

        $employee = Employee::query()->find($leaveTypeAssignment->employee_id);

        return [
            'row_number' => $leaveTypeAssignment->row_number,
            'id' => $leaveTypeAssignment->id,
            'employee_leave_type_id' => $leaveTypeAssignment->employee_leave_type_id,
            'employee_id' => $leaveTypeAssignment->employee_id,
            'leave_type_id' => $leaveTypeAssignment->leave_type_id,

            'employee_number' => $employee->number,
            'employee_full_name' => $employee->full_name,
            'employee_current_employment_profile' => $currentEmploymentProfile,
            'employee_department' => $employee->department,
            'employee_designation' => $employee->designation,

            'leave_type_ulid' => $leaveTypeAssignment->leave_type_ulid,
            'leave_type_code' => $leaveTypeAssignment->leave_type_code,
            'leave_type_name' => $leaveTypeAssignment->leave_type_name,
            'leave_type_initial_balance_upon_eligibility' => $leaveTypeAssignment->leave_type_initial_balance_upon_eligibility,

            //Leave type assignment settings
            'leave_type_assignment_override_balance_upon_eligibility' => $leaveTypeAssignment->leave_type_assignment_override_balance_upon_eligibility,
            'leave_type_assignment_balance_upon_eligibility' => $leaveTypeAssignment->leave_type_assignment_balance_upon_eligibility,
        ];
    }
}
