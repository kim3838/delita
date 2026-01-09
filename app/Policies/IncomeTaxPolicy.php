<?php

namespace App\Policies;

use App\Models\IncomeTax;
use App\Models\User;

class IncomeTaxPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'create-payroll-component');
    }

    public function update(User $user, IncomeTax $incomeTax): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, $incomeTax->company->id)
            && $this->hasPermission($user, 'update-payroll-component');
    }

    public function delete(User $user, IncomeTax $incomeTax): bool
    {
        return $user->isSuperAdmin();
    }
}
