<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'));
    }

    public function update(User $user, Shift $shift): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'));
    }

    public function delete(User $user, Shift $shift): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'));
    }
}
