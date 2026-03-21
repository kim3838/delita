<?php

namespace App\Policies;

use App\Blueprint\RequestInterface;
use App\Models\User;

class LeaveRunningBalanceReportPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'view-leave-running-balance');
    }
}
