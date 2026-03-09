<?php

namespace App\Transformers\LeaveTypeAssignment;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Facades\Fractal;
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

        $leaveTypeCodes = empty($leaveTypesByEmployees->assigned_leave_type_codes)
            ? null
            : explode(',', $leaveTypesByEmployees->assigned_leave_type_codes);

        return [
            'row_number' => $leaveTypesByEmployees->row_number,
            'id' => $leaveTypesByEmployees->employee_id,
            'employee_id' => $leaveTypesByEmployees->employee_id,

            'employee_number' => $leaveTypesByEmployees->employee_number,
            'employee_full_name' => $leaveTypesByEmployees->employee_full_name,
            'employee_current_employment_profile' => $currentEmploymentProfile,

            'assigned_leave_type_codes' =>$leaveTypeCodes
        ];
    }
}
