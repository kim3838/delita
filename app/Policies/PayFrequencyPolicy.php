<?php

namespace App\Policies;

use App\Models\PayFrequency;
use App\Models\User;

class PayFrequencyPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'view-payroll-frequency');
    }

    public function update(User $user, PayFrequency $payFrequency): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'update-payroll-frequency');
    }
}
