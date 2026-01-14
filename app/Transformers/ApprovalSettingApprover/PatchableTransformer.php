<?php

namespace App\Transformers\ApprovalSettingApprover;

use App\Concrete\TransformerAbstractConcrete;
use App\Enums\CompanyUserAssignmentType;
use App\Models\ApprovalSettingApprover;
use App\Models\Employee;

class PatchableTransformer extends TransformerAbstractConcrete
{
    public function transform(ApprovalSettingApprover $model): array
    {
        $filters = json_decode(request()->get('filters'));

        $user = $model->approver;

        $associatedCompanies = ($filters->company_id ?? false)
            ? $user->companies->whereIn('id', $filters->company_id)->values()
            : $user->companies;

        $mappedAssociatedCompanies = collect($this->mapAssociatedCompanies($associatedCompanies));

        $accountRoles = request()->account_id
            ? $this->collectionSummary($user->roles->where('account_id', request()->account_id)->values(), 'name', '')
            : null;

        $companyEmployeeNumber = '';
        $companyEmployeeAssignmentType = '';
        $companyEmployeeFullName = '';

        if($mappedAssociatedCompanies->first()){

            if($mappedAssociatedCompanies->first()['is_employee']){
                $companyEmployeeNumber = $mappedAssociatedCompanies->first()['employee_number'];
                $companyEmployeeFullName = $mappedAssociatedCompanies->first()['employee_full_name'];
            }

            $companyEmployeeAssignmentType = $mappedAssociatedCompanies->first()['assignment']['value'] == CompanyUserAssignmentType::ADMIN->value
                ? $mappedAssociatedCompanies->first()['assignment']['text']
                : '';
        }

        return [
            'id' => $model->id,
            'approval_setting_id' => $model->approval_setting_id,
            'order' => $model->order,
            'approver_id' => $model->approver_id,
            'approver_username' => $model->approver->name,
            'company_employee_number' => $companyEmployeeNumber,
            'company_employee_full_name' => $companyEmployeeFullName,
            'company_assignment_type' => $companyEmployeeAssignmentType,

            'account_roles_summary' => $accountRoles,
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
                    'assignment' => CompanyUserAssignmentType::tryFrom($assignedCompany->pivot->assignment_type)?->toArray() ?? null,
                    'is_employee' => (bool)$employee,
                    'employee_number' => $employee?->number,
                    'employee_full_name' => $employee?->full_name,
                ];
            })->toArray();
    }
}
