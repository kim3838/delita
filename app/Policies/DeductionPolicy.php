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

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'create-payroll-component');
    }

    public function update(User $user, Deduction $deduction): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, $deduction->company->id)
            && $this->hasPermission($user, 'update-payroll-component');
    }

    public function delete(User $user, Deduction $deduction): bool
    {
        return $user->isSuperAdmin();
    }
}
