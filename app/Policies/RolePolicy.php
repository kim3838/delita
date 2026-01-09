<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->hasPermission($user, 'view-role');
    }

    public function view(User $user, Role $role): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->hasPermission($user, 'update-role', $role->account_id);
    }

    public function create(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->hasPermission($user, 'create-role');
    }

    public function update(User $user, Role $role): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->hasPermission($user, 'update-role');
    }

    public function delete(User $user, Role $role): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->hasPermission($user, 'delete-role');
    }

    public function batchDelete(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->hasPermission($user, 'delete-role');
    }
}
