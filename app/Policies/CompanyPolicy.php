<?php

namespace App\Policies;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;

class CompanyPolicy
{
    public function create(User $user): bool
    {
        $isAdminInAnyCompany = (bool)CompanyUser::where('user_id', $user->id)
            ->where('assignment_type', CompanyUserAssignmentType::ADMIN->value)
            ->count();

        return $user->isSuperAdmin() || $isAdminInAnyCompany ;
    }

    public function update(User $user, Company $company): bool
    {
        $isAdminInCompany = (bool)CompanyUser::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->where('assignment_type', CompanyUserAssignmentType::ADMIN->value)
            ->count();

        return $user->isSuperAdmin() || $isAdminInCompany;
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isSuperAdmin();
    }
}
