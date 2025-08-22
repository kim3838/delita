<?php

namespace App\Policies;

use App\Models\Formula;
use App\Models\User;

class FormulaPolicy
{
    public function viewAny(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Formula $formula)
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Formula $formula): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Formula $formula): bool
    {
        return $user->isSuperAdmin();
    }
}
