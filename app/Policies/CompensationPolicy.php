<?php

namespace App\Policies;

use App\Models\Compensation;
use App\Models\User;

class CompensationPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'));
    }

    public function update(User $user, Compensation $compensation): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, $compensation->company->id);
    }

    public function delete(User $user, Compensation $compensation): bool
    {
        return $user->isSuperAdmin();
    }
}
