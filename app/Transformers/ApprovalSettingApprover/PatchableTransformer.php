<?php

namespace App\Transformers\ApprovalSettingApprover;

use App\Concrete\TransformerAbstractConcrete;
use App\Enums\ApproverType;
use App\Enums\CompanyUserAssignmentType;
use App\Models\ApprovalSettingApprover;
use App\Transformers\User\ListTransformer as UserListTransformer;

class PatchableTransformer extends TransformerAbstractConcrete
{
    public function transform(ApprovalSettingApprover $model): array
    {
        $filters = json_decode(request()->get('filters'));

        $companyId = null;
        $companyName = null;
        $companyEmployeeAssignmentType = null;
        $isEmployee = false;
        $companyEmployeeNumber = null;
        $companyEmployeeFullName = null;
        $accountRoles = null;

        if($model->type->value == ApproverType::SELECTED->value){

            $user = $model->approver;

            $associatedCompanies = ($filters->company_id ?? false)
                ? $user->companies->whereIn('id', $filters->company_id)->values()
                : $user->companies;

            $mappedAssociatedCompanies = collect(new UserListTransformer()->mapAssociatedCompanies($associatedCompanies));

            $accountRoles = request()->account_id
                ? $this->collectionSummary($user->roles->where('account_id', request()->account_id)->values(), 'name', '')
                : null;

            if($mappedAssociatedCompanies->first()){

                $companyId = $mappedAssociatedCompanies->first()['id'];
                $companyName = $mappedAssociatedCompanies->first()['name'];

                $isEmployee = $mappedAssociatedCompanies->first()['is_employee'];

                if($isEmployee){
                    $companyEmployeeNumber = $mappedAssociatedCompanies->first()['employee_number'];
                    $companyEmployeeFullName = $mappedAssociatedCompanies->first()['employee_full_name'];
                }

                $companyEmployeeAssignmentType = $mappedAssociatedCompanies->first()['assignment']['value'] == CompanyUserAssignmentType::ADMIN->value
                    ? $mappedAssociatedCompanies->first()['assignment']
                    : '';
            }
        }

        return [
            'id' => $model->id,
            'approval_setting_id' => $model->approval_setting_id,
            'order' => $model->order,
            'type' => $model->type?->toArray(),
            'type_value' => $model->type?->value,
            'approver_id' => $model->approver_id,
            'approver_username' => $model->approver?->name,

            'company_id' => $companyId,
            'company_name' => $companyName,
            'company_assignment_type' => $companyEmployeeAssignmentType,
            'is_employee' => $isEmployee,
            'company_employee_number' => $companyEmployeeNumber,
            'company_employee_full_name' => $companyEmployeeFullName,

            'account_roles_summary' => $accountRoles,
        ];
    }
}
