<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Account $account)
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Account $account): bool
    {
        return $user->isSuperAdmin();
    }
}
