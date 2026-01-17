<?php

namespace App\Transformers\Employee;

use App\Facades\Fractal;
use App\Models\Employee;
use App\Transformers\EmploymentProfile\ListTransformer as EmploymentProfileListTransformer;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'ulid' => $employee->ulid,
            'user_id' => $employee->user_id,
            'user' => $employee->user,
            'department' => $employee->departments->first(),
            'designation' => $employee->designation,
            'designation_id' => $employee->designation_id,
            'manager_id' => $employee->manager_id,
            'number' => $employee->number,
            'full_name' => $employee->full_name,
            'given_name' => $employee->given_name,
            'middle_name' => $employee->middle_name,
            'family_name' => $employee->family_name,
            'birth_date' => Carbon::parse($employee->birth_date)->toDateString(),
            'gender' => $employee->gender?->toArray(),
            'marital_status' => $employee->marital_status?->toArray(),
            'contact' => $employee->contact,
            'employment_profiles' => Fractal::collection($employee->employmentProfiles, EmploymentProfileListTransformer::class)['data']
        ];
    }
}
