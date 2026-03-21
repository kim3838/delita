<?php

namespace App\Policies;

use App\Blueprint\RequestInterface;
use App\Models\User;

class RequestApprovalStatePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'view-approval-states');
    }
}
