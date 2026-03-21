<?php

namespace App\Traits;

use App\Blueprint\RequestInterface;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

trait HasPolicy
{
    protected function isActionAuthorized($action, $model): bool
    {
        return Gate::allows($action, $model);
    }

    protected function hasPermission(?User $user, string $permission, $accountId = null): bool
    {
        $permitted = false;
        $accountId = empty($accountId)
            ? app(RequestInterface::class)->accountId
            : $accountId;

        $userRoles = $user->roles->where('account_id', $accountId);

        foreach ($userRoles as $role){

            $permitted = $role->permissions->contains('name', $permission);

            if($permitted) break;
        }

        return $permitted;
    }
}
