<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInAnyCompany($user);
    }

    public function view(User $user, User $stagedUser): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        $userIsTheOneWhoCreatedTheStagedUser = $stagedUser->created_by == $user->id;

        return $this->userIsAdminInAnyCompany($user);
    }

    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInAnyCompany($user);
    }

    public function update(User $user, User $stagedUser): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        $userIsTheOneWhoCreatedTheStagedUser = $stagedUser->created_by == $user->id;

        return $this->userIsAdminInAnyCompany($user);
    }
}
