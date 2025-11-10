<?php

namespace App\Transformers\Employee;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Blueprint\Repositories\UserRepository;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Transformers\EmploymentProfile\CurrentEmploymentProfileTransformer;
use App\Transformers\User\ItemTransformer as UserItemTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Employee $employee): array
    {
        $employeeUserHydrated = App::make(UserRepository::class)->hydrateItem([
            'name' => $employee->user_name,
            'email' => $employee->user_email,
            'email_verified_at' => $employee->user_email_verified_at,
            'status' => $employee->user_status,
        ]);

        $employeeUser = Fractal::item($employeeUserHydrated, UserItemTransformer::class);

        $currentEmploymentProfileHydrated = App::make(EmploymentProfileRepository::class)->hydrateItem([
            'id' => $employee->employment_profile_id,
            'employee_id' => $employee->employment_profile_employee_id,
            'is_active' => $employee->employment_status_active,
            'status' => $employee->current_employment_status,
            'employment_type' => $employee->current_employment_type,
            'start_date' => $employee->current_employment_start_date,
            'end_of_service_type' => $employee->end_of_service_type,
            'end_date' => $employee->current_employment_end_date,
        ]);

        $currentEmploymentProfile = Fractal::item($currentEmploymentProfileHydrated, CurrentEmploymentProfileTransformer::class);

        return [
            'id' => $employee->id,
            'ulid' => $employee->ulid,
            'row_number' => $employee->row_number,
            'number' => $employee->number,
            'full_name' => $employee->full_name,
            'gender' => $employee->gender?->toArray(),
            'marital_status' => $employee->marital_status?->toArray(),
            'department' => $employee->department,
            'designation' => $employee->designation,
            'manager' => $employee->manager,
            'contact' => $employee->contact,
            'current_employment_profile' => $currentEmploymentProfile,
            'user' => $employeeUser
        ];
    }
}
