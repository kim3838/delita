<?php

namespace App\Transformers\ShiftAssignment;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Facades\Fractal;
use App\Models\Hydrations\Employee\ShiftsByEmployees;
use App\Transformers\EmploymentProfile\CurrentEmploymentProfileTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ShiftsByEmployeesTransformer extends TransformerAbstract
{
    public function transform(ShiftsByEmployees $shiftsByEmployees): array
    {
        $currentEmploymentProfileHydrated = App::make(EmploymentProfileRepository::class)->hydrateItem([
            'employee_id' => $shiftsByEmployees->employee_id,
            'is_active' => $shiftsByEmployees->employee_employment_status_active,
            'status' => $shiftsByEmployees->employee_current_employment_status,
            'employment_type' => $shiftsByEmployees->employee_current_employment_type,
        ]);

        $currentEmploymentProfile = Fractal::item($currentEmploymentProfileHydrated, CurrentEmploymentProfileTransformer::class);

        $shiftCodes = empty($shiftsByEmployees->assigned_shift_codes)
            ? null
            : explode(',', $shiftsByEmployees->assigned_shift_codes);

        return [
            'row_number' => $shiftsByEmployees->row_number,
            'id' => $shiftsByEmployees->employee_id,
            'employee_id' => $shiftsByEmployees->employee_id,

            'employee_number' => $shiftsByEmployees->employee_number,
            'employee_full_name' => $shiftsByEmployees->employee_full_name,
            'employee_current_employment_profile' => $currentEmploymentProfile,

            'assigned_shift_codes' => $shiftCodes,
        ];
    }
}
