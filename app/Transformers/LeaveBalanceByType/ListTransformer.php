<?php

namespace App\Transformers\LeaveBalanceByType;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Concrete\LeaveService;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Transformers\EmploymentProfile\CurrentEmploymentProfileTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Employee $employee): array
    {
        $currentEmploymentProfileHydrated = App::make(EmploymentProfileRepository::class)->hydrateItem([
            'is_active' => $employee->employment_status_active,
            'status' => $employee->current_employment_status,
        ]);

        $currentEmploymentProfile = Fractal::item($currentEmploymentProfileHydrated, CurrentEmploymentProfileTransformer::class);

        $leaveBalanceByType = [
            'id' => $employee->id,
            'employee' => [
                'number' => $employee->number,
                'full_name' => $employee->full_name,
                'department' => $employee->department,
                'designation' => $employee->designation,
            ],
            'current_employment_profile' => $currentEmploymentProfile,
        ];

        $date = $employee->leave_balance_by_type_date;

        foreach ($employee->leave_balance_by_type_ulids as $ulid){

            $leaveType = LeaveType::query()->where('ulid', $ulid)->firstOrFail();

            $dateSeries = App::make(LeaveService::class)->getRunningBalanceByDate($employee, $leaveType, $date);

            $leaveBalanceByType[$ulid] = (float)$dateSeries['running_balance'];
        }

        return $leaveBalanceByType;
    }
}
