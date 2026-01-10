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
        $user = User::query()->find($model->user_id);
        $associatedCompanies = $user->companies->sortBy('code')->values();

        $mappedAssociatedCompanies = $associatedCompanies
            ->map(function($assignedCompany){

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

        $associatedCompaniesSummary = [
            'value' => 'None',
            'extender' => ''
        ];

        if($associatedCompanies->count() == 1){
            $associatedCompaniesSummary['value'] = $associatedCompanies->first()->short_name;
        } else if($associatedCompanies->count() > 1){
            $associatedCompaniesSummary['value'] = $associatedCompanies->first()->short_name;
            $associatedCompaniesSummary['extender'] = ' +' . ($associatedCompanies->count() - 1) . ' more';
        }

        return [
            'id' => $model->user_id,
            'ulid' => $model->user_ulid,
            'username' => $model->user_username,
            'email' => $model->user_email,
            'status' => $model->user_status?->toArray(),
            'email_verified_at' => $model->user_email_verified_at,
            'timezone' => $model->user_timezone,
            'created_by' => $user->createdBy?->name ?? '',
            'associated_companies' => $mappedAssociatedCompanies,
            'account_roles' => $model->account_roles,
            'associated_companies_summary' => $associatedCompaniesSummary
        ];
    }
}
