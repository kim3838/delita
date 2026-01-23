<?php

namespace App\Policies;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Traits\HasPolicy;

class BasePolicy
{
    use HasPolicy;

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
}
