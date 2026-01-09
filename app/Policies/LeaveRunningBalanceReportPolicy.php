<?php

namespace App\Policies;

use App\Models\User;

class LeaveRunningBalanceReportPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'view-leave-running-balance');
    }
}
