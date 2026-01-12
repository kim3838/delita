<?php

namespace App\Policies;

use App\Models\Overtime;
use App\Models\User;

class OvertimePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'view-overtime');
    }

    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'create-overtime');
    }

    public function update(User $user, Overtime $overtime): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'update-overtime');
    }

    public function batchDelete(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'delete-overtime');
    }
}
