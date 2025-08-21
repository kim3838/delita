<?php

namespace App\Policies;

use App\Models\Deduction;
use App\Models\User;

class DeductionPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'));
    }

    public function update(User $user, Deduction $deduction): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, $deduction->company->id);
    }

    public function delete(User $user, Deduction $deduction): bool
    {
        return $user->isSuperAdmin();
    }
}
