<?php

namespace App\Policies;

use App\Blueprint\RequestInterface;
use App\Models\User;

class OvertimeRequestPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'view-overtime-request');
    }

    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'create-overtime-request');
    }

    public function batchDelete(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'delete-overtime-request');
    }
}
