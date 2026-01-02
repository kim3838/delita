<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInAnyCompany($user);
    }

    public function view(User $user, Company $company): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, $company->id);
    }

    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInAnyCompany($user);
    }

    public function update(User $user, Company $company): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, $company->id);
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isSuperAdmin();
    }
}
