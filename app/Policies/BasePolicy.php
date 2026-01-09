<?php

namespace App\Policies;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;

class BasePolicy
{
    protected function userIsAdminInCompany(User $user, $companyId): bool
    {
        if(empty($companyId)){
            return false;
        }

        return Company::query()
                ->findOrFail($companyId)
                ->users
                ->findOrfail($user->id)
                ->pivot
                ->assignment_type == CompanyUserAssignmentType::ADMIN->value;
    }

    protected function userIsAdminInAnyCompany(User $user): bool
    {
        return (bool)CompanyUser::query()
            ->where('user_id', $user->id)
            ->where('assignment_type', CompanyUserAssignmentType::ADMIN->value)
            ->count();
    }

    protected function hasPermission(User $user, string $permission, $accountId = null): bool
    {
        $permitted = false;
        $accountId = empty($accountId) ? request()->input('account_id') : $accountId;
        $userRoles = $user->roles->where('account_id', $accountId);

        foreach ($userRoles as $role){

            $permitted = $role->permissions->contains('name', $permission);

            if($permitted) break;
        }

        return $permitted;
    }
}
