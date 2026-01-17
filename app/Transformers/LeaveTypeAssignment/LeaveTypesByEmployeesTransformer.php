<?php

namespace App\Transformers\LeaveTypeAssignment;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\Hydrations\Employee\LeaveTypesByEmployees;
use App\Transformers\EmploymentProfile\CurrentEmploymentProfileTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class LeaveTypesByEmployeesTransformer extends TransformerAbstract
{
    public function transform(LeaveTypesByEmployees $leaveTypesByEmployees): array
    {
        $currentEmploymentProfileHydrated = App::make(EmploymentProfileRepository::class)->hydrateItem([
            'employee_id' => $leaveTypesByEmployees->employee_id,
            'is_active' => $leaveTypesByEmployees->employee_employment_status_active,
            'status' => $leaveTypesByEmployees->employee_current_employment_status,
            'employment_type' => $leaveTypesByEmployees->employee_current_employment_type,
        ]);

        $currentEmploymentProfile = Fractal::item($currentEmploymentProfileHydrated, CurrentEmploymentProfileTransformer::class);

        $employee = Employee::query()->find($leaveTypesByEmployees->employee_id);

        return [
            'row_number' => $leaveTypesByEmployees->row_number,
            'id' => $leaveTypesByEmployees->employee_id,
            'employee_id' => $leaveTypesByEmployees->employee_id,

            'employee_number' => $employee->number,
            'employee_full_name' => $employee->full_name,
            'employee_current_employment_profile' => $currentEmploymentProfile,
            'employee_department' => $employee->departments->first(),
            'employee_designation' => $employee->designation,

            'assigned_leave_type_codes' => $leaveTypesByEmployees->assigned_leave_type_codes,
        ];
    }
}
