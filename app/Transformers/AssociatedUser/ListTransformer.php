<?php

namespace App\Transformers\AssociatedUser;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Employee;
use App\Models\Hydrations\AssociatedUser;
use App\Models\User;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(AssociatedUser $model): array
    {
        $associatedCompanies = User::query()->find($model->user_id)
            ->companies->map(function($assignedCompany){

                $employee = Employee::query()
                    ->where('user_id', $assignedCompany->pivot->user_id)
                    ->where('company_id', $assignedCompany->id)
                    ->first();

                return [
                    'name' => $assignedCompany->name,
                    'assignment' => CompanyUserAssignmentType::tryFrom($assignedCompany->pivot->assignment_type)?->toArray() ?? null,
                    'is_employee' => (bool)$employee,
                    'employee_number' => $employee?->number,
                    'employee_full_name' => $employee?->full_name,
                ];
            });

        return [
            'id' => $model->user_id,
            'ulid' => $model->user_ulid,
            'username' => $model->user_username,
            'email' => $model->user_email,
            'status' => $model->user_status?->toArray(),
            'email_verified_at' => $model->user_email_verified_at,
            'timezone' => $model->user_timezone,
            'associated_companies' => $associatedCompanies
        ];
    }
}
