<?php

namespace App\Transformers\User;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Employee;
use App\Models\User;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(User $model): array
    {
        $associatedCompanies = $model
            ->companies->map(function($assignedCompany){

                $employee = Employee::where('user_id', $assignedCompany->pivot->user_id)
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
            'id' => $model->id,
            'ulid' => $model->ulid,
            'username' => $model->username,
            'email' => $model->email,
            'status' => $model->status?->toArray(),
            'email_verified_at' => $model->email_verified_at,
            'timezone' => $model->timezone,
            'associated_companies' => $associatedCompanies
        ];
    }
}
