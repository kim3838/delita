<?php

namespace App\Transformers\User;

use App\Concrete\TransformerAbstractConcrete;
use App\Enums\CompanyUserAssignmentType;
use App\Models\Employee;
use App\Models\User;

class ListTransformer extends TransformerAbstractConcrete
{
    public function transform(User $model): array
    {
        $filters = json_decode(request()->get('filters'));

        $associatedCompanies = $model->companies->sortBy('code')->values();

        $mappedAssociatedCompanies = $this->mapAssociatedCompanies($associatedCompanies);

        $roles = !empty($filters->account_ids) && is_array($filters->account_ids)
            ? $model->roles->whereIn('account_id', $filters->account_ids)
            : $model->roles;

        $rolesByAccountNumber = $roles
            ->sortBy('account_id')
            ->values()
            ->map(function($role){
                return ['account_number' => $role->account->number, 'role' => $role->name];
            })->groupBy(function($item){
                return $item['account_number'];
            })->map(function($item, $key){
                return '...' . substr($key, -8) . '(' . $item->pluck('role')->join(', ', ' and ') . ')';
            })
            ->values()
            ->all();

        $accountRoles = $this->arraySummary($rolesByAccountNumber);

        $associatedCompaniesSummary = $this->collectionSummary($associatedCompanies, 'short_name');

        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'username' => $model->username,
            'email' => $model->email,
            'status' => $model->status?->toArray(),
            'email_verified_at' => $model->email_verified_at?->toDateTimeString(),
            'email_verified' => !empty($model->email_verified_at),
            'timezone' => $model->timezone,
            'created_by' => $model->createdBy?->name ?? '',
            'associated_companies' => $mappedAssociatedCompanies,
            'account_roles_summary' => $accountRoles,
            'associated_companies_summary' => $associatedCompaniesSummary
        ];
    }

    public function mapAssociatedCompanies($associatedCompanies): array
    {
        return $associatedCompanies
            ->map(function($assignedCompany){

                $employee = Employee::query()
                    ->where('user_id', $assignedCompany->pivot->user_id)
                    ->where('company_id', $assignedCompany->id)
                    ->first();

                return [
                    'id' => $assignedCompany->id,
                    'name' => $assignedCompany->name,
                    'assignment' => CompanyUserAssignmentType::tryFrom($assignedCompany->pivot->assignment_type)?->toArray() ?? null,
                    'is_employee' => (bool)$employee,
                    'employee_number' => $employee?->number,
                    'employee_full_name' => $employee?->full_name_attribute,
                ];
            })->toArray();
    }
}
